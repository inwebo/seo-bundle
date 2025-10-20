<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Service\Metadata;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Model\Command\AbstractImporter;
use Inwebo\SeoBundle\Model\MetadataInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class Importer extends AbstractImporter
{
    public function __construct(
        private readonly RouterInterface $router,
        private readonly EntityManagerInterface $entityManager,
        /** @var class-string<MetadataInterface> */
        private readonly string $entityFQCN,
        /** @var array<int, string> */
        private readonly array $excludedRoutes,
    ) {
        parent::__construct($this->router, $this->entityManager, $this->entityFQCN);
    }

    /**
     * @return array<int, MetadataInterface>
     */
    public function getEntities(): array
    {
        /** @var array<int, MetadataInterface> $entities */
        $entities = $this->entities;

        return $entities;
    }

    public function isValid(string $routeName, Route $route): bool
    {
        foreach ($this->excludedRoutes as $excludedRoute) {
            if (str_starts_with($routeName, $excludedRoute)) {
                return false;
            }
        }

        return true;
    }
}
