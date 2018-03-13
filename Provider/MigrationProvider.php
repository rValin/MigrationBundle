<?php

namespace RValin\MigrationBundle\Provider;

use RValin\MigrationBundle\Tools\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class MigrationProvider
 * @package RValin\MigrationBundle\Provider
 */
class MigrationProvider
{
    protected $_kernel;
    protected $_container;
    protected $_repository;

    public function __construct(KernelInterface $kernel, ContainerInterface $container)
    {
        $this->_kernel = $kernel;
        $this->_container = $container;
        $this->_repository = $container->get('doctrine.orm.entity_manager')
            ->getRepository('RValinMigrationBundle:RValinMigration')
        ;
    }

    /**
     * get all the migration to execute matching the bundle and the filters
     *
     * @param null  $bundleName
     * @param array $filters
     *
     * @return MigrationInterface[]
     */
    public function getMigrations($bundleName = null, array $filters)
    {
        $bundles = $this->getBundles($bundleName);
        $migrations = $this->getMigrationsClass($bundles);

        return $this->getMigrationsToExecute($migrations, $filters);
    }


    /**
     * instantiate the migrations class
     *
     * @param Bundle $bundle
     * @param array  $files
     *
     * @return array
     */
    protected function instantiateMigrations(Bundle $bundle, array $files)
    {
        $migrations = [];

        foreach($files as $file)
        {
            preg_match('#.+/([a-zA-Z0-9]+).php$#', $file, $match);
            $class = $bundle->getNamespace().'\Migration\\'.$match[1];
            $migrations[] = new $class($this->_container);
        }

        return $migrations;
    }

    /**
     * get Migrations from a list of bundles
     *
     * @param array $bundles
     *
     * @return MigrationInterface[]
     */
    protected function getMigrationsClass(array $bundles)
    {
        $migrations = [];
        foreach ($bundles as $bundle)
        {
            $dir = $bundle->getPath().'/Migration';
            $bundleMigrations = $this->instantiateMigrations($bundle, glob($dir.'/*.php', GLOB_BRACE));
            $migrations = \array_merge($migrations, $bundleMigrations);
        }
        return $migrations;
    }

    /**
     * return an array of bundles
     *
     * @param null $bundleName
     *
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    protected function getBundles($bundleName = null)
    {
        if ( null !== $bundleName )
        {
            return [$this->_kernel->getBundle($bundleName)];
        }

        return $this->_kernel->getBundles();
    }


    /**
     * filter migrations to return only matching migrations
     *
     * @param array $migrations
     * @param array $filters
     *
     * @return MigrationInterface[]
     *
     * @throws \InvalidArgumentException
     */
    protected function getMigrationsToExecute(array $migrations, array $filters)
    {
        $filters = $this->checkFilters($filters);

        $validMigrations = [];

        foreach ($migrations as $migration)
        {
            if (!$migration instanceof MigrationInterface) {
                throw new \InvalidArgumentException(sprintf('migrations should be an instance of %s', MigrationInterface::class));
            }

            // check the migration name
            if (null !== $filters['name'] && $migration->getName() !== $filters['name']) {
                continue;
            }

            // check the migration max version
            if (null !== $filters['maxVersion'] && $migration->getMigrationVersion() > $filters['maxVersion']) {
                continue;
            }
            // check the migration min version
            if (null !== $filters['minVersion'] && $migration->getMigrationVersion() < $filters['minVersion']) {
                continue;
            }

            // check the migration max date
            if (null !== $filters['maxDate'] && $migration->getCreationDate() > $filters['maxDate']) {
                continue;
            }

            // check the migration min date
            if (null !== $filters['minDate'] && $migration->getCreationDate() < $filters['minDate']) {
                continue;
            }

            // check if this migration has already been done
            if (true === $filters['notExecuted'] && $this->_repository->findOneBy(['migrationName' => $migration->getName()])) {
                continue;
            }

            if (false === $filters['notExecuted'] && !$this->_repository->findOneBy(['migrationName' => $migration->getName()])) {
                continue;
            }

            $validMigrations[] = $migration;
        }

        return $this->orderMigrations($validMigrations);
    }

    /**
     * check filters args
     * @param array $filters
     *
     * @return array
     */
    protected function checkFilters(array $filters)
    {
        $defaultFilters = [
            'name' => null,
            'maxVersion' => null,
            'minVersion' => null,
            'maxDate' => null,
            'minDate' => null,
            'notExecuted' => true,
        ];

        return array_merge($defaultFilters, $filters);
    }

    /**
     * sort the array of migration to execute the lowest version / creation date first
     * @param array $migrations
     *
     * @return array
     */
    protected function orderMigrations(array $migrations)
    {
        usort($migrations, function(MigrationInterface $a, MigrationInterface $b) {
            if ($a->getMigrationVersion() === $b->getMigrationVersion()) {
                return $a->getCreationDate() > $b->getCreationDate();
            }

            return $a->getMigrationVersion() > $b->getMigrationVersion();
        });

        return $migrations;
    }
}