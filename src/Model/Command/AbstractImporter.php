<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class AbstractImporter implements ImporterInterface
{
    protected array $entities = [];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $entityFQCN,
    ) {
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function create(string $routeName, Route $route): object
    {
        return (new $this->entityFQCN())->setRoute($routeName);
    }

    public function isValid(string $routeName, Route $route): bool
    {
        return false;
    }

    public function entityExists(string $routeName): bool
    {
        return !is_null($this
            ->entityManager
            ->getRepository($this->entityFQCN)
            ->findOneBy([
                'route' => $routeName,
            ]));
    }

    public function import(): static
    {
        foreach ($this->router->getRouteCollection() as $routeName => $route) {
            if ($this->isValid($routeName, $route)) {
                if (!$this->entityExists($routeName)) {
                    $entity = $this->create($routeName, $route);

                    $this->entityManager->persist($entity);
                    $this->entityManager->flush();

                    $this->entities[] = $entity;
                }
            }
        }

        return $this;
    }
}
