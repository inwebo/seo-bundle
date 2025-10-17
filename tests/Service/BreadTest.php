<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Inwebo\SeoBundle\Entity\Breadcrumb as EntityBreadcrumb;
use Inwebo\SeoBundle\Model\BreadcrumbBag;
use Inwebo\SeoBundle\Service\Bread;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[CoversClass(Bread::class)]
final class BreadTest extends TestCase
{
    protected ?string $template = null;
    protected Environment $environment;

    public function setUp(): void
    {
        $this->template = <<<TWIG
{{ breadcrumbs|length }}::[{% for b in breadcrumbs %}{{ b.name|raw }}|{{ b.url }}|{{ b.title|raw }}{% if not loop.last %};{% endif %}{% endfor %}]
TWIG;
        $this->environment = new Environment(new ArrayLoader(['@InweboSeo/_breadcrumbs.html.twig' => $this->template]));
    }

    private function createEvent(string $route, array $attributes = []): ControllerArgumentsEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request([], [], array_merge(['_route' => $route], $attributes));
        $controller = static fn (int $page = 2) => null;

        return new ControllerArgumentsEvent($kernel, $controller, $attributes, $request, HttpKernelInterface::MAIN_REQUEST);
    }

    private function createRepositoryStub(array $byRoute): object
    {
        return new class($byRoute) extends EntityRepository {
            public function __construct(private array $byRoute)
            {
            }

            public function findOneBy(array $criteria, ?array $orderBy = null): ?object
            {
                $route = $criteria['route'] ?? null;

                return $route && isset($this->byRoute[$route]) ? $this->byRoute[$route] : null;
            }
        };
    }

    public function testCrumbsBuildsHierarchyAndRenders(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->exactly(3))
            ->method('generate')
            ->willReturnOnConsecutiveCalls(
                [
                    'blog_paginated',
                    ['page' => 2, 'total' => 10],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ],
                [
                    'blog',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ],
                [
                    'homepage',
                    [],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ],
            )
            ->willReturnOnConsecutiveCalls(
                'https://example.test/blog/page-2',
                'https://example.test/blog',
                'https://example.test/'
            );

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $crumbPaginated = (new EntityBreadcrumb())
            ->setRouteName('blog_paginated')
            ->setName('Page {{ page }} / {{ total }}')
            ->setParent('blog')
            ->setTitle('My blog paginated')
            ->setRouteParameters(['page', 'total']);

        $crumbBlog = (new EntityBreadcrumb())
            ->setRouteName('blog')
            ->setName('Blog')
            ->setParent('homepage')
            ->setTitle('My blog');

        $crumbHome = (new EntityBreadcrumb())
            ->setRouteName('homepage')
            ->setName('Homepage')
            ->setParent(null)
            ->setTitle('My homepage');

        $repository = $this->createRepositoryStub([
            'blog_paginated' => $crumbPaginated,
            'blog' => $crumbBlog,
            'homepage' => $crumbHome,
        ]);

        $entityManager->method('getRepository')->willReturn($repository);

        // Add global twig vars via BreadcrumbBag
        $bag = new BreadcrumbBag();
        $bag->add(['total' => 10]);

        $bread = new Bread($this->environment, $entityManager, $bag, EntityBreadcrumb::class, $urlGenerator);

        // Simulate the kernel event capturing arguments and request
        $event = $this->createEvent('blog_paginated', ['page' => 2]);
        $bread->onKernelControllerArguments($event);

        // Act
        $html = $bread->crumbs();

        // Assert
        $this->assertSame('3::[Homepage|https://example.test/|My homepage;Blog|https://example.test/blog|My blog;Page 2 / 10|https://example.test/blog/page-2|My blog paginated]', $html);
    }

    public function testBreadcrumbNameDoesNotContainHtmlEntities(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('https://example.test/csv');

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $name = "Un reader de fichier CSV rapide comme l'éclair";
        $crumb = (new EntityBreadcrumb())
            ->setRouteName('csv_reader')
            ->setName($name)
            ->setParent(null)
            ->setTitle('CSV Reader');

        $repository = $this->createRepositoryStub([
            'csv_reader' => $crumb,
        ]);

        $entityManager->method('getRepository')->willReturn($repository);

        $bag = new BreadcrumbBag();
        $bread = new Bread($this->environment, $entityManager, $bag, EntityBreadcrumb::class, $urlGenerator);

        $event = $this->createEvent('csv_reader');
        $bread->onKernelControllerArguments($event);

        // Act
        $html = $bread->crumbs();

        // Assert
        // The expected output should contain the raw single quote, not the HTML entity &#039;
        $this->assertStringContainsString($name, $html);
        $this->assertStringNotContainsString('&#039;', $html);
    }

    public function testNoBreadcrumbForRouteRendersEmptyList(): void
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($this->createRepositoryStub([]));
        $bag = new BreadcrumbBag();
        $bread = new Bread($this->environment, $entityManager, $bag, EntityBreadcrumb::class, $urlGenerator);
        $event = $this->createEvent('unknown_route');
        $bread->onKernelControllerArguments($event);

        $html = $bread->crumbs();
        $this->assertSame('0::[]', $html);
    }
}
