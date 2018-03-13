<?php

namespace RValin\MigrationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class RunMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configuration de la commande migration:script:run
     */
    protected function configure()
    {
        $this->setName('migration:script:run')
            ->setDescription('Generate migration')
            ->addOption('bundle',  null, InputOption::VALUE_OPTIONAL, 'bundle')
            ->addOption('migration', null, InputOption::VALUE_OPTIONAL, 'Migration\'s name')
            ->addOption('execute', null, InputOption::VALUE_NONE, 'Execute the scripts')
            ->addOption('maxVersion', null, InputOption::VALUE_OPTIONAL, 'Maximum version of the migration to run')
            ->addOption('maxDate', null, InputOption::VALUE_OPTIONAL, 'Maximum creation date of the migration to run')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $migrationName = $input->getOption('migration');
        $bundle = $input->getOption('bundle');
        $execute = $input->getOption('execute');
        $maxVersion = $input->getOption('maxVersion');

        $maxDate = $input->getOption('maxDate');
        if (null != $maxDate) {
            $maxDate = new \DateTime($maxDate);
        }

        $migrationProvider = $this->getContainer()->get('rvalin.migration.provider');
        $migrationManager = $this->getContainer()->get('rvalin.migration.manager');
        $migrationManager->setConsoleOutput($output);

        $migrations = $migrationProvider->getMigrations($bundle, [
            'name' => $migrationName,
            'maxVersion' => $maxVersion,
            'maxDate' => $maxDate,
            'notExecuted' => true,
        ]);

        if(!$execute) {
            $output->writeln('<question>You did not use option --execute therefore command and query won\'t be executed</question>');
        }

        $output->writeln(sprintf('<info>%d migration(s) to run</info>', count($migrations)));
        $migrationManager->executeMigrations($migrations, $execute);

        if(!$execute) {
            $output->writeln('<question>Run this command with the option --execute to execute these migrations</question>');
        }
    }
}