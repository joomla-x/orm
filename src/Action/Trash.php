<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Action;

class Trash extends AbstractAction
{
	public function __invoke($entity)
	{
		\NXDebug::_(__METHOD__, '+');
		$meta = $this->repository->getMeta();

		if (!empty($meta->fieldAliases['state']))
		{
			$property            = $meta->propertyName($meta->fieldAliases['state']);
			$entity->{$property} = -2;
		}
		\NXDebug::_(__METHOD__, '-');
	}
}
