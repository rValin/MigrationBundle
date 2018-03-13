<?php

namespace RValin\MigrationBundle\Tools;

use Symfony\Component\Console\Output\OutputInterface;

interface MigrationInterface
{
    /**
     * Execute the migration
     * @return boolean should return a boolean to determine if the migration has succeed. If null is returned this will be considered a success.
     */
    public function execute();

    /**
     * Reverse the migration
     * @return boolean should return a boolean to determine if the reverse has succeed. If null is returned this will be considered a success.
     */
    public function reverse();

    /**
     * return the name of this migration
     * @return string
     */
    public function getName();

    /**
     * return the date when this migration was created
     * @return mixed
     */
    public function getCreationDate();

    /**
     * return the version of this migration
     * @return mixed
     */
    public function getMigrationVersion();

    /**
     * set the console output
     * @param OutputInterface $output
     *
     * @return mixed
     */
    public function setConsoleOutput(OutputInterface $output);

    /**
     * Set the execute property
     * @param $execute
     *
     * @return mixed
     */
    public function setExecute($execute);
}