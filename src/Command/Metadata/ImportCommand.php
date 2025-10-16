<?php

declare(strict_types=1);

namespace Inwebo\SeoBundle\Command\Metadata;

use Inwebo\SeoBundle\Entity\Metadata;
use Inwebo\SeoBundle\Service\Metadata\Importer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'inwebo_seo:import:metadata',
    description: 'Update missing metadata items from routes configuration.',
    aliases: ['is:i:m'],
)]
class ImportCommand extends Command
{
    public function __construct(private readonly Importer $importer)
    {
        parent::__construct('inwebo_seo:import:metadata');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importer->import();
        /** @var array<Metadata> $entities */
        $entities = $this->importer->getEntities();

        if (!empty($entities)) {
            $table = new Table($output);
            $table->setStyle('box');
            $table->setHeaders(['id', 'route', 'title', 'description', 'h1']);
            foreach ($this->importer->getEntities() as $metadata) {
                $table->addRow([
                    $metadata->getId(),
                    $metadata->getRoute(),
                    $metadata->getTitle(),
                    $metadata->getDescription(),
                    $metadata->getH1(),
                ]);
            }
            $table->render();

            $output->writeln(sprintf('<info>%s new metadata persisted.</info>', count($this->importer->getEntities())));
        } else {
            $output->writeln('<info>No new metadata found.</info>');
        }

        return Command::SUCCESS;
    }
}
