<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Service\Breadcrumbs;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Entity\Breadcrumb;
use Inwebo\SeoBundle\Service\Breadcrumbs\Importer;
use Inwebo\SeoBundle\Tests\Fixtures\src\RouterTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(Importer::class)]
#[Group('InweboSeoBundle')]
class ImporterTest extends TestCase
{
    use RouterTrait;

    private Importer $importer;

    public function setUp(): void
    {
        $router = $this->getRouter('routes_breadcrumbs.yaml');
        $this->importer = new Importer(
            $router,
            $this->createMock(EntityManagerInterface::class),
            Breadcrumb::class
        );
    }

    public function testBreadcrumbsImport(): void
    {
        $entities = $this->importer->import()->getEntities();

        $this->assertCount(3, $entities);

        $this->assertInstanceOf(Breadcrumb::class, $entities[0]);
        $this->assertEquals('homepage', $entities[0]->getRoute());
        $this->assertEquals('Homepage', $entities[0]->getName());
        $this->assertNull($entities[0]->getParent());
        $this->assertEquals('My homepage', $entities[0]->getTitle());
        $this->assertCount(0, $entities[0]->getRouteParameters());

        $this->assertInstanceOf(Breadcrumb::class, $entities[1]);
        $this->assertEquals('blog', $entities[1]->getRoute());
        $this->assertEquals('Blog', $entities[1]->getName());
        $this->assertEquals('homepage', $entities[1]->getParent());
        $this->assertEquals('My blog', $entities[1]->getTitle());
        $this->assertCount(0, $entities[1]->getRouteParameters());

        $this->assertInstanceOf(Breadcrumb::class, $entities[2]);
        $this->assertEquals('blog_paginated', $entities[2]->getRoute());
        $this->assertEquals('Page {{ page }} / {{ total }}', $entities[2]->getName());
        $this->assertEquals('blog', $entities[2]->getParent());
        $this->assertEquals('My blog paginated', $entities[2]->getTitle());
        $this->assertCount(1, $entities[2]->getRouteParameters());
        $this->assertEquals('page', $entities[2]->getRouteParameters()[0]);
    }
}
