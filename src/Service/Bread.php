<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Entity\Breadcrumb;
use Inwebo\SeoBundle\Model\BagInterface as Bag;
use Inwebo\SeoBundle\Model\Dto;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error;

class Bread extends AbstractSeoService
{
    /**
     * @var array<int,Dto\Breadcrumb>
     */
    private array $breadcrumbs = [];

    public function __construct(
        Environment $environment,
        EntityManagerInterface $entityManager,
        Bag $bag,
        string $entityFQCN,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
        parent::__construct($environment, $entityManager, $bag, $entityFQCN);
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST === $event->getRequestType() && null === $this->controllerArgumentsEvent) {
            $this->controllerArgumentsEvent = $event;
        }
    }

    /**
     * @return array<string,mixed>
     */
    protected function getRouteParameters(Breadcrumb $breadcrumb): array
    {
        return array_intersect_key($this->getTwigVariables(), array_flip($breadcrumb->getRouteParameters()));
    }

    /**
     * @throws Error\LoaderError
     * @throws Error\SyntaxError
     */
    protected function createTemplateName(Breadcrumb $breadcrumb): string
    {
        return html_entity_decode(
            $this
                ->environment
                ->createTemplate($breadcrumb->getName() ?? '')
                ->render($this->getTwigVariables()),
            encoding: 'UTF-8'
        );
    }

    /**
     * @throws Error\LoaderError
     * @throws Error\SyntaxError
     */
    protected function createTemplateTitle(Breadcrumb $breadcrumb): string
    {
        return html_entity_decode(
            $this
                ->environment
                ->createTemplate($breadcrumb->getTitle() ?? '')
                ->render($this->getTwigVariables()),
            encoding: 'UTF-8'
        );
    }

    /**
     * @throws Error\LoaderError
     * @throws Error\SyntaxError
     */
    protected function bake(?string $routeName = null): void
    {
        if (is_null($routeName) && null !== $this->controllerArgumentsEvent) {
            $routeName = $this->controllerArgumentsEvent->getRequest()->attributes->get('_route');

            if (null === $routeName) {
                return;
            }
        }

        /** @var ?Breadcrumb $breadCrumb */
        $breadCrumb = $this->entityManager->getRepository($this->entityFQCN)->findOneBy(['route' => $routeName]);

        if (null === $breadCrumb) {
            return;
        }

        try {
            $url = $this->urlGenerator->generate(
                $routeName, // @phpstan-ignore argument.type
                $this->getRouteParameters($breadCrumb),
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } catch (\Exception $e) {
            $url = '';
        }

        $this->breadcrumbs[] = new Dto\Breadcrumb(
            $this->createTemplateName($breadCrumb),
            $url,
            $this->createTemplateTitle($breadCrumb),
        );

        if (null !== $breadCrumb->getParent()) {
            $this->bake($breadCrumb->getParent());
        }
    }

    /**
     * @throws Error\LoaderError
     * @throws Error\RuntimeError
     * @throws Error\SyntaxError
     */
    public function crumbs(): string
    {
        $this->bake();

        return $this->environment
            ->render('@InweboSeo/_breadcrumbs.html.twig', [
                'breadcrumbs' => array_reverse($this->breadcrumbs),
            ]);
    }
}
