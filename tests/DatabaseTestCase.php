<?php

namespace Joomla\Tests;

use PDO;
use PHPUnit\DbUnit\Database\Connection;
use PHPUnit\DbUnit\TestCase;

/**
 * Class DatabaseTestCase
 *
 * @package Joomla\Tests
 */
abstract class DatabaseTestCase extends TestCase
{
    static private $pdo = null;

    private $conn = null;

    /**
     * Returns the test database connection.
     *
     * @return Connection
     */
    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new PDO($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWD']);
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $_ENV['DB_DBNAME']);
        }

        return $this->conn;
    }
}
