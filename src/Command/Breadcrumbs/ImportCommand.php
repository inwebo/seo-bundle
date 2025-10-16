<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Command\Breadcrumbs;

use Inwebo\SeoBundle\Service\Breadcrumbs\Importer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'inwebo_seo:import:breadcrumbs',
    description: 'Update missing breadcrumb items from routes configuration.',
    aliases: ['is:i:b'],
)]
class ImportCommand extends Command
{
    public function __construct(private readonly Importer $importer)
    {
        parent::__construct('inwebo_seo:import:breadcrumbs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importer->import();

        if (!empty($this->importer->getEntities())) {
            $table = new Table($output);
            $table->setStyle('box');
            $table->setHeaders(['id', 'route', 'name', 'title', 'parent', 'route parameters']);
            foreach ($this->importer->getEntities() as $breadcrumb) {
                $table->addRow([
                    $breadcrumb->getId(),
                    $breadcrumb->getRoute(),
                    $breadcrumb->getName(),
                    $breadcrumb->getTitle(),
                    $breadcrumb->getParent(),
                    implode(', ', $breadcrumb->getRouteParameters()),
                ]);
            }
            $table->render();

            $output->writeln(sprintf('<info>%s new breadcrumbs persisted.</info>', count($this->importer->getEntities())));
        } else {
            $output->writeln('<info>No new breadcrumbs found.</info>');
        }

        return Command::SUCCESS;
    }
}
