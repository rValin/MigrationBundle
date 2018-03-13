<?php

namespace RValin\MigrationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ReverseMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configuration de la commande migration:script:run
     */
    protected function configure()
    {
        $this->setName('migration:script:reverse')
            ->setDescription('Reverse migrations')
            ->addOption('bundle',  null, InputOption::VALUE_OPTIONAL, 'bundle')
            ->addOption('migration', null, InputOption::VALUE_OPTIONAL, 'Migration\'s name')
            ->addOption('execute', null, InputOption::VALUE_NONE, 'Execute the scripts')
            ->addOption('minVersion', null, InputOption::VALUE_OPTIONAL, 'Version until which migration should be reversed')
            ->addOption('minDate', null, InputOption::VALUE_OPTIONAL, 'Date until which migration should be reversed')
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
        $minVersion = $input->getOption('minVersion');

        $minDate = $input->getOption('minDate');
        if (null != $minDate) {
            $minDate = new \DateTime($minDate);
        }

        $migrationProvider = $this->getContainer()->get('rvalin.migration.provider');
        $migrationManager = $this->getContainer()->get('rvalin.migration.manager');
        $migrationManager->setConsoleOutput($output);

        $migrations = $migrationProvider->getMigrations($bundle, [
            'name' => $migrationName,
            'minVersion' => $minVersion,
            'minDate' => $minDate,
            'notExecuted' => false,
        ]);

        if(!$execute) {
            $output->writeln('<question>You did not use option --execute therefore command and query won\'t be reversed</question>');
        }

        $output->writeln(sprintf('<info>%d migration(s) to reverse</info>', count($migrations)));
        $migrationManager->reverseMigrations($migrations, $execute);

        if(!$execute) {
            $output->writeln('<question>Run this command with the option --execute to reverse these migrations</question>');
        }
    }
}