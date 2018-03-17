<?php

namespace RValin\MigrationBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use RValin\MigrationBundle\Entity\RValinMigration;
use RValin\MigrationBundle\Tools\MigrationInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MigrationManager
 * @package RValin\MigrationBundle\Manager
 */
class MigrationManager
{
    /**
     * @var NullOutput
     */
    protected $_output;

    /**
     * @var EntityManagerInterface
     */
    protected $_entityManager;

    /**
     * MigrationManager constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->_output = new NullOutput();
        $this->_entityManager = $entityManager;
    }

    /**
     * @param OutputInterface $output
     */
    public function setConsoleOutput(OutputInterface $output)
    {
        $this->_output = $output;
    }

    /**
     * Run a migration
     *
     * @param MigrationInterface $migration
     * @param                    $execute
     */
    public function executeMigration(MigrationInterface $migration, $execute)
    {
        $this->_output->writeln(sprintf('<comment>Execute migration: %s</comment>', $migration->getName()));

        $migration->setExecute($execute);
        $migration->setConsoleOutput($this->_output);

        $result = $migration->execute();
        if ($result || null === $result) {

            if ($execute) {
                $this->_output->writeln(sprintf('<info>Migration executed: %s</info>', $migration->getName()));
                $this->saveMigrationExecuted($migration);
            }
        } else {
            $this->_output->writeln(sprintf('<error>Migration failed: %s</error>', $migration->getName()));
        }
    }

    /**
     * Execute multiple migrations
     *
     * @param array $migrations
     * @param       $execute
     */
    public function executeMigrations(array $migrations, $execute)
    {
        foreach ($migrations as $migration)
        {
            if (!$migration instanceof MigrationInterface){
                throw new \InvalidArgumentException(sprintf('migrations should be an instance of %s', MigrationInterface::class));
            }
            $this->executeMigration($migration, $execute);
        }
    }

    /**
     * Reverse multiple migrations
     *
     * @param array $migrations
     * @param       $execute
     */
    public function reverseMigrations(array $migrations, $execute)
    {
        foreach ($migrations as $migration)
        {
            if (!$migration instanceof MigrationInterface){
                throw new \InvalidArgumentException(sprintf('migrations should be an instance of %s', MigrationInterface::class));
            }
            $this->reverseMigration($migration, $execute);
        }
    }

    /**
     * Reverse a migration
     *
     * @param MigrationInterface $migration
     * @param                    $execute
     */
    public function reverseMigration(MigrationInterface $migration, $execute)
    {
        $this->_output->writeln(sprintf('<comment>Reverse migration: %s</comment>', $migration->getName()));

        $migration->setExecute($execute);
        $migration->setConsoleOutput($this->_output);

        $result = $migration->reverse();
        if ($result || null === $result) {

            if ($execute) {
                $this->_output->writeln(sprintf('<info>Migration reversed: %s</info>', $migration->getName()));
                $this->saveMigrationReversed($migration);
            }
        } else {
            $this->_output->writeln(sprintf('<error>Reverse failed: %s</error>', $migration->getName()));
        }
    }

    /**
     * @param MigrationInterface $migration
     */
    protected function saveMigrationExecuted(MigrationInterface $migration)
    {
        $rvalinMigration = New RValinMigration();
        $rvalinMigration->setMigrationDate(new \DateTime());
        $rvalinMigration->setMigrationName($migration->getName());
        $this->_entityManager->persist($rvalinMigration);
        $this->_entityManager->flush($rvalinMigration);
    }

    /**
     * @param MigrationInterface $migration
     */
    protected function saveMigrationReversed(MigrationInterface $migration)
    {
        $rvalinMigration = $this->_entityManager->getRepository('RValinMigrationBundle:RValinMigration')->findOneBy(['migrationName' => $migration->getName()]);
        if ($rvalinMigration instanceof RValinMigration) {
            $this->_entityManager->remove($rvalinMigration);
            $this->_entityManager->flush($rvalinMigration);
        }
    }
}