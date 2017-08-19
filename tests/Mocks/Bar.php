<?php
/**
 * Part of the Joomla Framework ORM Package Test Suite
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Tests\Mocks;

/**
 * Mocks a class with a non-standard Id property
 */
class Bar
{
    /** @var int The Id */
    private $bar_id = -1;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->bar_id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->bar_id = $id;
    }
}
