<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Service\Breadcrumbs;

use Inwebo\SeoBundle\Entity\Breadcrumb;
use Inwebo\SeoBundle\Model\BreadcrumbInterface;
use Inwebo\SeoBundle\Model\Command\AbstractImporter;
use Symfony\Component\Routing\Route;

/**
 * @extends AbstractImporter<BreadcrumbInterface>
 */
class Importer extends AbstractImporter
{
    /**
     * @return list<BreadcrumbInterface>
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    public function isValid(string $routeName, ?Route $route = null): bool
    {
        if (!is_null($route) && !$route->hasOption(Breadcrumb::CONFIG_KEY)) {
            return false;
        }

        /** @var array<string, string|null> $breadcrumbOptions */
        $breadcrumbOptions = $route->getOption(Breadcrumb::CONFIG_KEY); // @phpstan-ignore method.nonObject

        return array_key_exists('name', $breadcrumbOptions)
            && array_key_exists('parent', $breadcrumbOptions)
            && array_key_exists('title', $breadcrumbOptions);
    }

    public function create(string $routeName, Route $route): object
    {
        /** @var BreadcrumbInterface $entity */
        $entity = parent::create($routeName, $route);

        /**
         * @var array{
         *     title: string,
         *     parent: ?string,
         *     name: string,
         * } $breadcrumbOptions
         */
        $breadcrumbOptions = $route->getOption(Breadcrumb::CONFIG_KEY);

        $entity
            ->setTitle($breadcrumbOptions['title'])
            ->setParent($breadcrumbOptions['parent'])
            ->setName($breadcrumbOptions['name'])
            ->setRouteParameters($route->compile()->getPathVariables()) // @phpstan-ignore argument.type
        ;

        return $entity;
    }
}
