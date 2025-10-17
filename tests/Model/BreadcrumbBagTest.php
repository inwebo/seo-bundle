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
        $this->breadcrumbBag::clear();
        $this->breadcrumbBag::add(['foo' => 'bar']);
        $this->assertCount(1, $this->breadcrumbBag::all());
        $this->assertEquals(['foo' => 'bar'], $this->breadcrumbBag::all());

        $this->breadcrumbBag = BreadcrumbBag::create();
        $this->assertCount(1, $this->breadcrumbBag::all());
        $this->assertEquals(['foo' => 'bar'], $this->breadcrumbBag::all());
        $this->breadcrumbBag::add(['bar' => 'foo']);

        $this->breadcrumbBag = BreadcrumbBag::create();
        $this->assertCount(2, $this->breadcrumbBag::all());
    }
}
