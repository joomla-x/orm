<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Tests\Storage;

use Joomla\ORM\Storage\Csv\CsvDataGateway;
use Joomla\ORM\Storage\Csv\CsvDataMapper;
use Joomla\ORM\Tests\Mocks\Article;

class CsvDataMapperTest extends DataMapperTestCases
{
	/** @var  CsvDataMapper */
	protected $dataMapper;

	/** @var  \PHPUnit_Framework_MockObject_MockObject|CsvDataGateway */
	protected $connection;

	public function setUp()
	{
		$this->connection = $this->createMock(CsvDataGateway::class);

		$this->connection
			->expects($this->any())
			->method('getAll')
			->with('articles')
			->willReturn(
				array_values($this->articles)
			);

		parent::setUp();

		$this->dataMapper = new CsvDataMapper($this->connection, Article::class, 'articles', $this->entityRegistry);
	}
}
