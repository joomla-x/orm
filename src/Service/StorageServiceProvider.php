<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Service;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\DebugStack;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\ORM\Storage\Doctrine\DoctrineTransactor;
use Psr\Container\ContainerInterface;

/**
 * Storage Service Provider.
 *
 * @package Joomla/ORM
 *
 * @since   __DEPLOY_VERSION__
 */
class StorageServiceProvider implements ServiceProviderInterface
{
    /**
     * @var string
     */
    private $configFile;

    /**
     * StorageServiceProvider constructor.
     *
     * @param $configFile
     */
    public function __construct($configFile = null)
    {
        $this->configFile = $configFile;
    }

    /**
     * Registers a RepositoryFactory with the container
     *
     * @param   Container $container The DI container
     * @param   string    $alias     An optional alias
     *
     * @return  void
     */
    public function register(Container $container, $alias = null)
    {
        $container->set('Repository', [$this, 'createRepositoryFactory'], true, true);

        if (!empty($alias)) {
            $container->alias($alias, 'Repository');
        }
    }

    /**
     * Creates a RepositoryFactory
     *
     * @param   ContainerInterface $container The container
     *
     * @return  RepositoryFactory
     */
    public function createRepositoryFactory(ContainerInterface $container)
    {
        if (empty($this->configFile)) {
            $this->configFile = $container->get('ConfigDirectory') . '/config/database.ini';
        }

        $config = parse_ini_file($this->configFile, true);

        $configuration = new Configuration;

        // Add logger
        $logger = new DebugStack;
        $configuration->setSQLLogger($logger);

        $connection = DriverManager::getConnection(
            [
                'url'     => $config['databaseUrl'],
                'charset' => 'utf8',
            ],
            $configuration
        );
        $transactor = new DoctrineTransactor($connection);

        $repositoryFactory = new RepositoryFactory($config, $connection, $transactor);

        if ($container->has('dispatcher')) {
            $repositoryFactory->setDispatcher($container->get('dispatcher'));
        }

        return $repositoryFactory;
    }
}
