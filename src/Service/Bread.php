<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Entity\Breadcrumb;
use Inwebo\SeoBundle\Model\BreadcrumbBag;
use Inwebo\SeoBundle\Model\Dto;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class Bread implements EventSubscriberInterface
{
    /**
     * @var array<int,Dto\Breadcrumb>
     */
    private array $breadcrumbs = [];

    private ?ControllerArgumentsEvent $controllerArgumentsEvent = null;

    public function __construct(
        private readonly Environment $environment,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $entityFQCN,
    ) {
    }

    protected function getControllerArguments(): array
    {
        return (null !== $this->controllerArgumentsEvent) ? $this->controllerArgumentsEvent->getArguments() : [];
    }

    protected function getTwigVariables(): array
    {
        $args = $this->getControllerArguments();
        foreach ((BreadcrumbBag::create())::getVars() as $key => $option) {
            $args[$key] = $option;
        }

        return $args;
    }

    protected function getRouteParameters(Breadcrumb $breadcrumb): array
    {
        $vars = array_merge($this->getControllerArguments(), $this->getTwigVariables());

        return array_intersect_key($vars, array_flip($breadcrumb->getRouteParameters()));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', -10],
        ];
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST === $event->getRequestType() && null === $this->controllerArgumentsEvent) {
            $this->controllerArgumentsEvent = $event;
        }
    }

    protected function createTemplateName(Breadcrumb $breadcrumb): string
    {
        return $this
            ->environment
            ->createTemplate($breadcrumb->getName() ?? '')
            ->render($this->getTwigVariables());
    }

    protected function createTemplateTitle(Breadcrumb $breadcrumb): string
    {
        return $this
            ->environment
            ->createTemplate($breadcrumb->getTitle() ?? '')
            ->render($this->getTwigVariables());
    }

    protected function bake(?string $routeName = null): void
    {
        if (is_null($routeName) && null !== $this->controllerArgumentsEvent) {
            $routeName = $this->controllerArgumentsEvent->getRequest()->attributes->get('_route');
        }

        /** @var ?Breadcrumb $breadCrumb */
        $breadCrumb = $this->entityManager->getRepository($this->entityFQCN)->findOneBy(['route' => $routeName]);

        if (null === $breadCrumb) {
            return;
        }

        $url = $this->urlGenerator->generate(
            $routeName,
            $this->getRouteParameters($breadCrumb),
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $this->breadcrumbs[] = new Dto\Breadcrumb(
            $this->createTemplateName($breadCrumb),
            $url,
            $this->createTemplateTitle($breadCrumb),
        );

        if (null !== $breadCrumb->getParent()) {
            $this->bake($breadCrumb->getParent());
        }
    }

    public function crumbs(): string
    {
        $this->bake();

        return $this->environment
            ->render('@InweboSeo/_breadcrumbs.html.twig', [
                'breadcrumbs' => $this->breadcrumbs,
            ]);
    }
}
