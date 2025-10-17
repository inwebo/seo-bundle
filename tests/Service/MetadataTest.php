<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Inwebo\SeoBundle\Entity\Metadata as EntityMetadata;
use Inwebo\SeoBundle\Model\MetadataBag;
use Inwebo\SeoBundle\Service\Metadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

#[CoversClass(Metadata::class)]
class MetadataTest extends TestCase
{
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

    private function createEvent(string $route, array $attributes = []): ControllerArgumentsEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request([], [], array_merge(['_route' => $route], $attributes));
        $controller = static fn (string $H1 = "from arg H1 controller", string $title = 'from title controller') => null;

        return new ControllerArgumentsEvent($kernel, $controller, $attributes, $request, HttpKernelInterface::MAIN_REQUEST);
    }

    public function setUp(): void
    {
        $this->environment = new Environment(new ArrayLoader([]));
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $homepage = new EntityMetadata()
            ->setRoute('homepage')
            ->setTitle('Title')
            ->setDescription('Description')
            ->setH1('H1')
        ;

        $template = new EntityMetadata()
            ->setRoute('template')
            ->setTitle('{{ title }}')
            ->setDescription('{{ description }}')
            ->setH1('{{ H1 }}')
        ;

        $repository = $this->createRepositoryStub([
            'homepage' => $homepage,
            'template' => $template,
        ]);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->method('getRepository')->willReturn($repository);

        $this->metadata = new Metadata(
            $this->environment,
            $this->entityManager,
            EntityMetadata::class,
            ['_']
        );
    }

    public function testTemplating(): void
    {
        $event = $this->createEvent('homepage');
        $this->metadata->onKernelControllerArguments($event);

        $this->assertEquals('H1', $this->metadata->getH1());
        $this->assertEquals('Title', $this->metadata->getTitle());
        $this->assertEquals('Description', $this->metadata->getDescription());
    }

    public function testTwig(): void
    {
        $event = $this->createEvent('template', [
            'H1' => 'from arg H1 controller',
            'title' => 'from title controller',
        ]);

        MetadataBag::create()::add(['description' => 'from MetadataBag']);
        $this->metadata->onKernelControllerArguments($event);

        $this->assertEquals('from arg H1 controller', $this->metadata->getH1());
        $this->assertEquals('from title controller', $this->metadata->getTitle());
        $this->assertEquals('from MetadataBag', $this->metadata->getDescription());
    }
}
