<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Model\BagInterface as Bag;
use Inwebo\SeoBundle\Model\MetadataInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

class Metadata extends AbstractSeoService
{
    protected ?MetadataInterface $entity = null;

    public function __construct(
        Environment $environment,
        EntityManagerInterface $entityManager,
        Bag $bag,
        string $entityFQCN,
        /**
         * @var array<string>
         */
        private readonly array $excludedRoutes,
    ) {
        parent::__construct($environment, $entityManager, $bag, $entityFQCN);
    }

    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST === $event->getRequestType() && null === $this->controllerArgumentsEvent) {
            $this->controllerArgumentsEvent = $event;

            $_route = $event->getRequest()->attributes->get('_route');

            foreach ($this->excludedRoutes as $excludedRoute) {
                if (is_string($_route) && str_starts_with($_route, $excludedRoute)) {
                    return;
                }
            }

            $metadata = $this->getEntity($_route); // @phpstan-ignore argument.type

            if ($metadata instanceof MetadataInterface) {
                $this->entity = $metadata;
            }
        }
    }

    /**
     * @return ?MetadataInterface
     *
     * @todo Fix return type
     */
    protected function getEntity(string $routeName): ?object
    {
        return $this->entityManager->getRepository($this->entityFQCN)->findOneBy(['route' => $routeName]); // @phpstan-ignore return.type
    }

    /**
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function getH1(): string
    {
        return $this
            ->environment
            ->createTemplate($this->entity?->getH1() ?? 'todo')
            ->render($this->getTwigVariables());
    }

    /**
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function getTitle(): string
    {
        return $this
            ->environment
            ->createTemplate($this->entity?->getTitle() ?? 'todo')
            ->render($this->getTwigVariables());
    }

    /**
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function getDescription(): string
    {
        return $this
            ->environment
            ->createTemplate($this->entity?->getDescription() ?? 'todo')
            ->render($this->getTwigVariables());
    }
}
