<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Model\MetadataBag;
use Inwebo\SeoBundle\Model\MetadataInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class Metadata implements EventSubscriberInterface
{
    protected MetadataInterface $entity;
    private ?ControllerArgumentsEvent $controllerArgumentsEvent = null;

    public function __construct(
        private readonly Environment $environment,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $entityFQCN,
        private readonly array $excludedRoutes,
    ) {
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

            foreach ($this->excludedRoutes as $excludedRoute) {
                if (str_starts_with($event->getRequest()->attributes->get('_route'), $excludedRoute)) {
                    return;
                }
            }

            $metadata = $this->getEntity($event->getRequest()->attributes->get('_route'));

            if ($metadata instanceof MetadataInterface) {
                $this->entity = $metadata;
            } else {
                throw new \Exception($event->getRequest()->attributes->get('_route').' is not a valid metadata object.');
            }
        }
    }

    protected function getControllerArguments(): array
    {
        return (null !== $this->controllerArgumentsEvent) ? $this->controllerArgumentsEvent->getNamedArguments() : [];
    }

    public function getTwigVariables(): array
    {
        $args = $this->getControllerArguments();
        foreach ((MetadataBag::create())::all() as $key => $option) {
            $args[$key] = $option;
        }

        return $args;
    }

    /**
     * @return ?MetadataInterface
     */
    protected function getEntity(string $routeName): ?object
    {
        return $this->entityManager->getRepository($this->entityFQCN)->findOneBy(['route' => $routeName]);
    }

    public function getH1(): string
    {
        return $this
            ->environment
            ->createTemplate($this->entity->getH1() ?? 'todo')
            ->render($this->getTwigVariables());
    }

    public function getTitle(): string
    {
        return $this
            ->environment
            ->createTemplate($this->entity->getTitle() ?? 'todo')
            ->render($this->getTwigVariables());
    }

    public function getDescription(): string
    {
        return $this
            ->environment
            ->createTemplate($this->entity->getDescription() ?? 'todo')
            ->render($this->getTwigVariables());
    }
}
