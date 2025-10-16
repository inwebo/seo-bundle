<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Tests\Command\Breadcrumbs;

use Doctrine\ORM\EntityManagerInterface;
use Inwebo\SeoBundle\Command\Breadcrumbs\ImportCommand;
use Inwebo\SeoBundle\Entity\Breadcrumb;
use Inwebo\SeoBundle\Service\Breadcrumbs\Importer;
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
        $router = $this->getRouter('routes_breadcrumbs.yaml');
        $importer = new Importer(
            $router,
            $this->createMock(EntityManagerInterface::class),
            Breadcrumb::class
        );
        $command = new ImportCommand($importer);

        $commandTester = new CommandTester($command);

        $commandTester->execute([]);
        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('3 new breadcrumbs persisted.', $output);
    }
}
