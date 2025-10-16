<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Command\Metadata;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Command\Metadata\ImportCommand;
use Inwebo\SeoBundle\Entity\Metadata;
use Inwebo\SeoBundle\Service\Metadata\Importer;
use Inwebo\SeoBundle\Tests\Fixtures\src\RouterTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(ImportCommand::class)]
#[Group('InweboSeoBundle')]
class ImportCommandTest extends TestCase
{
    use RouterTrait;

    public function testImport(): void
    {
        $router = $this->getRouter('routes_metadata.yaml');
        $importer = new Importer(
            $router,
            $this->createMock(EntityManagerInterface::class),
            Metadata::class,
            ['admin_'],
        );
        $command = new ImportCommand($importer);

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('1 new metadata persisted.', $output);
    }
}
