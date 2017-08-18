<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Storage\Doctrine;

use Doctrine\DBAL\Connection;
use Joomla\ORM\Entity\EntityRegistry;

/**
 * Class PrefixedDoctrineDataMapper
 *
 * @package  Joomla/ORM
 *
 * @since    __DEPLOY_VERSION__
 */
class PrefixedDoctrineDataMapper extends DoctrineDataMapper
{
    /**
     * DoctrineDataMapper constructor.
     *
     * @param   Connection     $connection     The database connection
     * @param   string         $entityClass    The class name of the entity
     * @param   string         $table          The table name
     * @param   EntityRegistry $entityRegistry The entity registry
     */
    public function __construct(Connection $connection, $entityClass, $table, EntityRegistry $entityRegistry)
    {
        $config = new \JConfig();
        $prefix = $config->dbprefix;
        parent::__construct($connection, $entityClass, $prefix . $table, $entityRegistry);
    }
}
