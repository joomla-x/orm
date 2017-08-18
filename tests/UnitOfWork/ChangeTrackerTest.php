<?php
/**
 * Part of the Joomla Framework ORM Package Test Suite
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Tests\UnitOfWork;

use Joomla\ORM\UnitOfWork\ChangeTracker;
use Joomla\ORM\Exception\OrmException;
use Joomla\ORM\Tests\Mocks\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests the change tracker
 */
class ChangeTrackerTest extends TestCase
{
	/** @var ChangeTracker The change tracker to use in tests */
	private $changeTracker = null;

	/** @var User An entity to use in the tests */
	private $entity1 = null;

	/** @var User An entity to use in the tests */
	private $entity2 = null;

	/**
	 * Sets up the tests
	 */
	public function setUp()
	{
		$this->changeTracker = new ChangeTracker();

		/**
		 * The Ids are purposely unique so that we can identify them as such without having to first insert them to
		 * assign unique Ids
		 * They are also purposely set to 724 and 1987 so that they won't potentially overlap with any default values
		 * set to the Ids
		 */
		$this->entity1 = new User(724, "foo");
		$this->entity2 = new User(1987, "bar");
	}

	/**
	 * Tests seeing if a change is detected with a comparison function
	 */
	public function testCheckingForChangeWithComparisonFunction()
	{
		$className = get_class($this->entity1);
		$this->changeTracker->startTracking($this->entity1);
		$this->changeTracker->startTracking($this->entity2);
		$this->entity1->setUsername("not entity 1's username");
		$this->changeTracker->registerComparator($className, function (User $a, User $b)
		{
			return $a->getId() == $b->getId();
		});
		$this->assertFalse($this->changeTracker->hasChanged($this->entity1));
	}

	/**
	 * Tests seeing if a change is detected without a comparison function
	 */
	public function testCheckingForChangeWithoutComparisonFunction()
	{
		$this->changeTracker->startTracking($this->entity1);
		$this->entity1->setUsername("blah");
		$this->assertTrue($this->changeTracker->hasChanged($this->entity1));
	}

	/**
	 * Tests checking for changes on an unregistered entity
	 */
	public function testCheckingForChangesOnUnregisteredEntity()
	{
		$this->expectException(OrmException::class);
		$this->changeTracker->hasChanged($this->entity1);
	}

	/**
	 * Tests checking that nothing has changed with a comparison function
	 */
	public function testCheckingForNoChangeWithComparisonFunction()
	{
		$className = get_class($this->entity1);
		$this->changeTracker->startTracking($this->entity1);
		$this->changeTracker->registerComparator($className, function ($a, $b)
		{
			return false;
		});
		$this->assertTrue($this->changeTracker->hasChanged($this->entity1));
	}

	/**
	 * Tests checking that nothing has changed without a comparison function
	 */
	public function testCheckingForNoChangeWithoutComparisonFunction()
	{
		$this->changeTracker->startTracking($this->entity1);
		$this->assertFalse($this->changeTracker->hasChanged($this->entity1));
	}
}
