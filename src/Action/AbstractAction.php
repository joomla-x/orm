<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Action;

use Joomla\ORM\Repository\RepositoryInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractAction
{
	/** @var  ContainerInterface */
	protected $repository;

	/**
	 * AbstractAction constructor.
	 *
	 * @param RepositoryInterface $repository
	 */
	public function __construct(RepositoryInterface $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * @param object $entity
	 *
	 * @return void
	 */
	abstract public function __invoke($entity);
}
