<?php

namespace RValin\MigrationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RValinMigration
 *
 * @ORM\Table(name="rvalin_migration")
 * @ORM\Entity(repositoryClass="RValin\MigrationBundle\Repository\RValinMigrationRepository")
 */
class RValinMigration
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="migration_date", type="datetime")
     */
    private $migrationDate;

    /**
     * @var string
     *
     * @ORM\Column(name="migration_name", type="string", length=255)
     */
    private $migrationName;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set migrationDate
     *
     * @param \DateTime $migrationDate
     *
     * @return RValinMigration
     */
    public function setMigrationDate($migrationDate)
    {
        $this->migrationDate = $migrationDate;

        return $this;
    }

    /**
     * Get migrationDate
     *
     * @return \DateTime
     */
    public function getMigrationDate()
    {
        return $this->migrationDate;
    }

    /**
     * @return string
     */
    public function getMigrationName()
    {
        return $this->migrationName;
    }

    /**
     * @param $migrationName
     *
     * @return $this
     */
    public function setMigrationName($migrationName)
    {
        $this->migrationName = $migrationName;

        return $this;
    }
}

