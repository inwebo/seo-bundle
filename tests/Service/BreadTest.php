<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Inwebo\SeoBundle\Entity\Breadcrumb;
use Inwebo\SeoBundle\Service\Bread;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(Bread::class)]
#[Group('InweboSeoBundle')]
class BreadTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    public function setUp(): void
    {
        static::bootKernel();
        $container = self::getContainer();
        $this->assertTrue($container->has(UrlGeneratorInterface::class));
        // $bread = static::$kernel->getContainer()->get(Bread::class);

        $this->em = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);

        $homepage = new Breadcrumb()
            ->setRoute('homepage')
            ->setName('Homepage')
            ->setTitle('My homepage');

        $blog = new Breadcrumb()
            ->setRoute('blog')
            ->setName('Blog')
            ->setParent('homepage')
            ->setTitle('My blog');

        $blogPaginated = new Breadcrumb()
            ->setRoute('blog_paginated')
            ->setName('Page {{ page }} / {{ total }}')
            ->setParent('blog')
            ->setTitle('My blog paginated');

        $repository
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls($blogPaginated, $blog, $homepage);

        $this->em->method('getRepository')->willReturn($repository);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        restore_error_handler();
        restore_exception_handler();
    }

    public function testEm(): void
    {
        $breadcrumb = $this->em->getRepository('foo')->findOneBy(['route', 'homepage']);

        $this->assertInstanceOf(Breadcrumb::class, $breadcrumb);
    }
}
