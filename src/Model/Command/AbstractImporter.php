<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model\Command;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Model\HasRouteNameInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @template TEntity of object
 *
 * @implements ImporterInterface<TEntity>
 */
class AbstractImporter implements ImporterInterface
{
    /**
     * @var list<TEntity>
     */
    protected array $entities = [];

    /**
     * @phpstan-param class-string<TEntity & HasRouteNameInterface> $entityFQCN
     */
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
        return (new $this->entityFQCN())->setRouteName($routeName);
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
                    /** @var TEntity $entity */
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
