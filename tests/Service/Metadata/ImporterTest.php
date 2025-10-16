<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Service\Metadata;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Entity\Metadata;
use Inwebo\SeoBundle\Service\Metadata\Importer;
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
        $router = $this->getRouter('routes_metadata.yaml');
        $this->importer = new Importer(
            $router,
            $this->createMock(EntityManagerInterface::class),
            Metadata::class,
            ['admin_'],
        );
    }

    public function testMetadataImport(): void
    {
        $entities = $this->importer->import()->getEntities();

        $this->assertCount(1, $entities);
        $this->assertNotEquals('admin', $entities[0]->getRoute());
        $this->assertEquals('homepage', $entities[0]->getRoute());
    }
}
