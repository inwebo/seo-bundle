<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Model\BagInterface as Bag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

abstract class AbstractSeoService implements EventSubscriberInterface
{
    protected ?ControllerArgumentsEvent $controllerArgumentsEvent = null;

    /**
     * @param class-string<object> $entityFQCN
     */
    public function __construct(
        protected readonly Environment $environment,
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Bag $bag,
        protected readonly string $entityFQCN,
    ) {
    }

    public function getBag(): Bag
    {
        return $this->bag;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => ['onKernelControllerArguments', -10],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getControllerArguments(): array
    {
        return (null !== $this->controllerArgumentsEvent) ? $this->controllerArgumentsEvent->getNamedArguments() : []; // @phpstan-ignore return.type
    }

    /**
     * @return array<string, mixed>
     */
    protected function getTwigVariables(): array
    {
        return array_merge($this->getControllerArguments(), $this->bag->all());
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
    }
}
