<?php
/**
 * Part of the Joomla Framework ORM Package Test Suite
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Tests\Mocks;

/**
 * Mocks a user object for use in testing
 */
class User
{
    /** @var int The user Id */
    public $id = -1;

    /** @var int The Id of an imaginary aggregate root (eg parent) of this user */
    public $aggregateRootId = -1;

    /** @var int The Id of a second imaginary aggregate root of this user */
    public $secondAggregateRootId = -1;

    /** @var string The username */
    public $username = "";

    /**
     * @param int    $id       The user Id
     * @param string $username The username
     */
    public function __construct($id = 0, $username = '')
    {
        $this->id       = $id;
        $this->username = $username;
    }

    /**
     * @return int
     */
    public function getAggregateRootId()
    {
        return $this->aggregateRootId;
    }

    /**
     * @param int $aggregateRootId
     */
    public function setAggregateRootId($aggregateRootId)
    {
        $this->aggregateRootId = $aggregateRootId;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getSecondAggregateRootId()
    {
        return $this->secondAggregateRootId;
    }

    /**
     * @param int $secondAggregateRootId
     */
    public function setSecondAggregateRootId($secondAggregateRootId)
    {
        $this->secondAggregateRootId = $secondAggregateRootId;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}
