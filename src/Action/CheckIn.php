<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Action;

use Joomla\ORM\Definition\Parser\BelongsTo;
use Joomla\ORM\Repository\RepositoryInterface;
use JUser;
use PEP\Entity\User;

class CheckIn extends AbstractAction
{
	/** @var  JUser */
	private $user;

	public function __construct(RepositoryInterface $repository, User $user)
	{
		parent::__construct($repository);

		$this->user = $user;
	}

	/**
	 * @param object $entity
	 */
	public function __invoke($entity)
	{
		$meta = $this->repository->getMeta();

		if (!empty($meta->fieldAliases['checked_out_by']))
		{
			$property = $meta->propertyName($meta->fieldAliases['checked_out_by']);

			if ($entity->{$property} > 0 && $entity->{$property} != $this->user->id && !$this->user->authorise('core.admin', 'com_checkin'))
			{
				throw new \RuntimeException("Entity was locked by another user.");
			}

			$entity->{$property} = 0;

			/** @var BelongsTo $relation */
			foreach ($meta->relations['belongsTo'] as $relation)
			{
				if ($relation->colIdName() == $meta->fieldAliases['checked_out_by'])
				{
					$property = $relation->varObjectName();
					$entity->{$property} = null;
					break;
				}
			}
		}

		if (!empty($meta->fieldAliases['checked_out_time']))
		{
			$property = $meta->propertyName($meta->fieldAliases['checked_out_time']);
			$entity->{$property} = null;
		}
	}
}
