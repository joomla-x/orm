<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Definition\Locator\Strategy;

/**
 * Class FileStrategy
 *
 * @package  Joomla/ORM
 *
 * @since    __DEPLOY_VERSION__
 */
class FileStrategy implements StrategyInterface
{
	/**
	 * Locate a definition file
	 *
	 * @param   string $filename The name of the XML file
	 *
	 * @return  string|null  The path, if found, null else
	 */
	public function locate($filename)
	{
		return file_exists($filename) ? $filename : null;
	}
}
