<?php

namespace RValin\MigrationBundle\Tools;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class MigrationGenerator
{
    /**
     * @var string
     */
    protected static $classTemplate =
        '<?php

namespace <namespace>;

use RValin\MigrationBundle\Tools\DefaultMigration;

class <migrationClassName> extends DefaultMigration
{
    /**
     * Migration
     */
    public function execute() 
    {
        // should return the result of the migration
        return true;
    }
    
    /**
     * Cancel the migration
     */
    public function reverse()
    {
        // should return the result of the reverse
        return false;
    }
    
    public function getCreationDate()
    {
        return new \DateTime(\'<migrationDate>\');
    }
    
    public function getMigrationVersion()
    {
        return \'<migrationVersion>\';
    }
    
    /**
     * @return string
     */
    public function getName() 
    {
        return \'<migrationName>\';
    }
}
';


    /**
     * Generate a migration file
     *
     * @param BundleInterface $bundle
     * @param                 $migrationName
     * @param null            $version
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function generateMigrationClass(BundleInterface $bundle, $migrationName, $version = null)
    {
        if (!ctype_alnum($migrationName)) {
            throw new \InvalidArgumentException(sprintf('Migration\'s name should contains only letters and numbers, %s given', $migrationName));
        }

        $content = $this->getMigrationContent($bundle, $migrationName, $version);

        $migrationFileName = $this->generateMigrationClassName($migrationName).'.php';
        $path = $bundle->getPath() . '/Migration/' . $migrationFileName;

        if (file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('A file with the name %s already exists', $migrationFileName));
        }

        $this->createClass($path, $content);

        return $path;
    }

    /**
     * get the namespace for the migration
     * @param BundleInterface $bundle
     *
     * @return string
     */
    protected function generateMigrationNamespace(BundleInterface $bundle)
    {
        return $bundle->getNamespace().'\Migration';
    }

    /**
     * get the name of the migration class
     * @param string $migrationName
     *
     * @return string
     */
    protected function generateMigrationClassName($migrationName)
    {
        return 'Migration'.$migrationName;
    }

    /**
     * get the content of the migration file to create
     * @param BundleInterface $bundle
     * @param                 $migrationName
     * @param null            $version
     *
     * @return string
     */
    protected function getMigrationContent(BundleInterface $bundle, $migrationName, $version = null)
    {

        $placeHolders = array(
            '<namespace>',
            '<migrationClassName>',
            '<migrationName>',
            '<migrationDate>',
            '<migrationVersion>',
        );

        $replacements = array(
            $this->generateMigrationNamespace($bundle),
            $this->generateMigrationClassName($migrationName),
            $migrationName,
            date('Y-m-d H:i:s'),
            $version
        );

        return str_replace($placeHolders, $replacements, static::$classTemplate) . "\n";
    }

    /**
     * Create the migration file
     * @param $path
     * @param $content
     */
    protected function createClass($path, $content)
    {
        $dir = dirname($path);
        if ( !is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($path, $content);
    }
}