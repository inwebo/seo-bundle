<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Model\Command;

use Symfony\Component\Routing\Route;

/**
 * @template TEntity of object
 */
interface ImporterInterface
{
    /**
     * @return list<TEntity>
     */
    public function getEntities(): array;

    public function isValid(string $routeName, Route $route): bool;

    public function create(string $routeName, Route $route): object;

    public function entityExists(string $routeName): bool;

    public function import(): static;
}
