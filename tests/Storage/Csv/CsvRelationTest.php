<?php
/**
 * Part of the Joomla Framework ORM Package Test Suite
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Tests\Storage\Csv;

use Joomla\ORM\Repository\Repository;
use Joomla\ORM\Storage\Csv\CsvDataGateway;
use Joomla\ORM\Storage\Csv\CsvDataMapper;
use Joomla\ORM\Storage\Csv\CsvTransactor;
use Joomla\ORM\Tests\Mocks\Detail;
use Joomla\ORM\Tests\Mocks\Extra;
use Joomla\ORM\Tests\Mocks\Master;
use Joomla\ORM\Tests\Mocks\Tag;
use Joomla\ORM\Tests\Storage\RelationTestCases;

class CsvRelationTest extends RelationTestCases
{
	protected function onBeforeSetup()
	{
		$dataPath = realpath(__DIR__ . '/../..');

		$this->config = parse_ini_file($dataPath . '/data/entities.csv.ini', true);

		$this->connection = new CsvDataGateway($this->config['dataPath']);
		$this->transactor = new CsvTransactor($this->connection);
	}

	protected function onAfterSetUp()
	{
		$entities = [Master::class, Detail::class, Extra::class, Tag::class];

		foreach ($entities as $className)
		{
			$meta                   = $this->builder->getMeta($className);
			$dataMapper             = new CsvDataMapper(
				$this->connection,
				$className,
				$meta->storage['table'],
				$this->entityRegistry
			);
			$this->repo[$className] = new Repository($className, $dataMapper, $this->unitOfWork);
		}
	}
}
