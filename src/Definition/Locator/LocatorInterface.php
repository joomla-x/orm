<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Definition\Locator;

use Joomla\ORM\Definition\Locator\Strategy\StrategyInterface;

/**
 * Interface LocatorInterface
 *
 * @package  Joomla/ORM
 *
 * @since    __DEPLOY_VERSION__
 */
interface LocatorInterface
{
	/**
	 * Finds the description file for an entity
	 *
	 * @param   string $entityName The name of the entity
	 *
	 * @return  string  Path to the XML file
	 */
	public function findFile($entityName);

	/**
	 * Adds a strategy
	 *
	 * @param   StrategyInterface $strategy The strategy
	 *
	 * @return  void
	 */
	public function add(StrategyInterface $strategy);
}
