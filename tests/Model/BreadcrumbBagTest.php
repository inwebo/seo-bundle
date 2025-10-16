<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Model;

use Inwebo\SeoBundle\Model\BreadcrumbBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[CoversClass(BreadcrumbBag::class)]
#[Group('InweboSeoBundle')]
class BreadcrumbBagTest extends TestCase
{
    private BreadcrumbBag $breadcrumbBag;

    public function testAddVar(): void
    {
        $this->breadcrumbBag = BreadcrumbBag::create();
        $this->breadcrumbBag::addVar(['foo' => 'bar']);
        $this->assertCount(1, $this->breadcrumbBag::getVars());
        $this->assertEquals(['foo' => 'bar'], $this->breadcrumbBag::getVars());
    }

    public function testGetVars(): void
    {
        $this->breadcrumbBag = BreadcrumbBag::create();
        $this->assertCount(1, $this->breadcrumbBag::getVars());
        $this->assertEquals(['foo' => 'bar'], $this->breadcrumbBag::getVars());
        $this->breadcrumbBag::addVar(['bar' => 'foo']);
    }

    public function testGetNewVars(): void
    {
        $this->breadcrumbBag = BreadcrumbBag::create();
        $this->assertCount(2, $this->breadcrumbBag::getVars());
    }
}
