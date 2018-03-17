<?php

namespace RValin\MigrationBundle\Tools;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class DefaultMigration implements MigrationInterface
{
    /**
     * @var ContainerInterface
     */
    protected $_container;

    /**
     * Causes the migration to be executed
     * @var bool
     */
    protected $_execute = false;

    /**
     * @var OutputInterface
     */
    protected $_output;

    /**
     * @inheritdoc
     */
    public function __construct(ContainerInterface $container)
    {
        $this->_container = $container;
        $this->_output = new NullOutput();
    }

    /**
     * @inheritdoc
     */
    public function setConsoleOutput(OutputInterface $output)
    {
        $this->_output = $output;
    }

    /**
     * @inheritdoc
     */
    public function setExecute($execute)
    {
        $this->_execute = $execute;
    }

    /**
     * allows to execute a SQL query
     *
     * @param       $sql
     * @param array $params
     *
     * @throws \Doctrine\DBAL\DBALException
     * @return boolean TRUE on success or FALSE on failure.
     */
    protected function executeSql($sql, array $params = array())
    {
        $this->_output->writeln(sprintf('<comment>Execute query: %s</comment>', $sql));

        $em = $this->_container->get('doctrine.orm.entity_manager');
        $connection = $em->getConnection();

        $statement = $connection->prepare($sql);
        foreach ($params as $key => $param) {
            $statement->bindValue($key, $param);
        }

        // continue only if execute is true
        if (!$this->_execute) {
            return true;
        }

        if($statement->execute()) {
            $this->_output->writeln('<info>Query executed</info>');
            return true;
        }

        $this->_output->writeln('<error>Query failed</error>');

        return false;
    }

    /**
     * Run a command
     *
     * @param array $commandArgs
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function executeCommand(array $commandArgs)
    {
        if( !array_key_exists('command', $commandArgs) ) {
            throw new \InvalidArgumentException(sprintf('The index "command" must be defined to execute a command (%s)', $this->getName()));
        }

        $commandStr = $commandArgs['command'];
        foreach ($commandArgs as $key => $arg) {
            if ( 'command' !== $key ) {
                $commandStr .= ' ' . $key . '=' . $arg;
            }
        }
        $this->_output->writeln(sprintf('<info>Execute command: %s</info>', $commandStr));

        $application = new Application($this->_container->get('kernel'));
        $application->setAutoExit(false);

        $input = new ArrayInput($commandArgs);

        // run the command only if execute is true
        if ( !$this->_execute) {
            return;
        }

        $application->run($input, $this->_output);
    }
}