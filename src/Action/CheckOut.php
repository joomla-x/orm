<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Action;

use DateTime;
use Joomla\ORM\Repository\RepositoryInterface;
use JUser;
use PEP\Entity\User;

class CheckOut extends AbstractAction
{
	/** @var  JUser */
	private $user;

	public function __construct(RepositoryInterface $repository, User $user)
	{
		parent::__construct($repository);

		$this->user = $user;
	}

	/**
	 * @param $entity
	 */
	public function __invoke($entity)
	{
		$meta = $this->repository->getMeta();

		if (!empty($meta->fieldAliases['checked_out_by']))
		{
			foreach ($meta->relations['belongsTo'] as $relation)
			{
				if ($relation->reference == $meta->fieldAliases['checked_out_by'])
				{
					$property = $meta->propertyName($relation->name);
					if (!empty($entity->{$property}) && $entity->{$property} != $this->user)
					{
						throw new \RuntimeException("Entity is already locked by another user.");
					}

					$entity->{$property} = $this->user;
					break;
				}
			}
		}

		if (!empty($meta->fieldAliases['checked_out_time']))
		{
			$property            = $meta->propertyName($meta->fieldAliases['checked_out_time']);
			$date                = (new DateTime('now'))->format('Y-m-d H:i:s');
			$entity->{$property} = $date;
		}
	}
}
