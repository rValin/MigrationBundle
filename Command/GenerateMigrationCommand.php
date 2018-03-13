<?php

namespace RValin\MigrationBundle\Command;

use RValin\MigrationBundle\Tools\MigrationGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configuration de la commande rvalin:executor:run
     */
    protected function configure()
    {
        $this->setName('migration:script:generate')
            ->setDescription('Create a new migration file')
            ->addOption('bundle', 'b', InputOption::VALUE_REQUIRED, 'bundle')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Migration\'s name')
            ->addOption('migrationVersion', null, InputOption::VALUE_OPTIONAL, 'Migration\'s version')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $kernel = $this->getContainer()->get('kernel');
        $bundle = $kernel->getBundle($input->getOption('bundle'));

        $migrationVersion = $input->getOption('migrationVersion');
        $migrationName = $input->getOption('name');
        if( empty($migrationName) ) {
            $migrationName = uniqid(date('Ymdhi'), false);
        }

        $generator = $this->getContainer()->get('rvalin.migration.generator');
        $file = $generator->generateMigrationClass($bundle, $migrationName, $migrationVersion);

        $output->writeln(sprintf('<info>Migration class generated : %s</info>', $file));
    }
}