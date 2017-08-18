<?php
namespace Joomla\ORM\Tests\Storage;

use Doctrine\DBAL\Connection;
use Joomla\ORM\Entity\EntityBuilder;
use Joomla\ORM\Entity\EntityRegistry;
use Joomla\ORM\Exception\EntityNotFoundException;
use Joomla\ORM\IdAccessorRegistry;
use Joomla\ORM\Operator;
use Joomla\ORM\Repository\RepositoryInterface;
use Joomla\ORM\Service\RepositoryFactory;
use Joomla\ORM\Storage\Csv\CsvDataGateway;
use Joomla\ORM\UnitOfWork\TransactionInterface;
use Joomla\ORM\UnitOfWork\UnitOfWorkInterface;
use Joomla\Tests\Unit\DumpTrait;
use Joomla\ORM\Tests\Mocks\Article;
use PHPUnit\Framework\TestCase;

class StorageTestCases extends TestCase
{
	/** @var  array */
	protected $config;

	/** @var  CsvDataGateway|Connection */
	protected $connection;

	/** @var  RepositoryInterface */
	protected $repo;

	/** @var EntityBuilder The entity builder */
	protected $builder;

	/** @var  IdAccessorRegistry */
	protected $idAccessorRegistry;

	/** @var  UnitOfWorkInterface */
	protected $unitOfWork;

	/** @var  TransactionInterface */
	protected $transactor;

	/** @var  EntityRegistry */
	protected $entityRegistry;

	use DumpTrait;

	public function setUp()
	{
		$repositoryFactory    = new RepositoryFactory($this->config, $this->connection, $this->transactor);
		$this->entityRegistry = $repositoryFactory->getEntityRegistry();
		$this->unitOfWork     = $repositoryFactory->getUnitOfWork();
	}

	/**
	 * @testdox Entity finder returns array on requested columns
	 */
	public function testEntityFinderReturnsArrayOnRequestedColumns()
	{
		$result = $this->repo
			->findOne()
			->columns(['*'])
			->with('id', Operator::EQUAL, 1)
			->getItem();

		$this->assertEquals('array', gettype($result));
	}

	/**
	 * @testdox Entity finder returns only requested columns
	 */
	public function testEntityFinderReturnsRequestedColumns()
	{
		$result = $this->repo
			->findOne()
			->columns(['id', 'title'])
			->with('id', Operator::EQUAL, 1)
			->getItem();

		$this->assertEquals(['id' => 1, 'title' => 'First Article'], $result);
	}

	/**
	 * @testdox Entity finder returns an Entity, if no columns are requested
	 */
	public function testEntityFinderReturnsEntityIfNoColumnsSpecified()
	{
		$result = $this->repo
			->findOne()
			->with('id', Operator::EQUAL, 1)
			->getItem();

		$this->assertTrue($result instanceof Article);
	}

	/**
	 * @testdox Columns can be specified as comma separated string
	 */
	public function testColumnsSpecifiedAsCommaSeparatedString()
	{
		$result = $this->repo
			->findOne()
			->columns('id, title')
			->with('id', Operator::EQUAL, 1)
			->getItem();

		$this->assertEquals(['id' => 1, 'title' => 'First Article'], $result);
	}

	/**
	 * @testdox Entity finder throws EntityNotFoundException, if no result is found
	 */
	public function testEntityFinderThrowsExceptionIfNoResult()
	{
		try
		{
			/** @noinspection PhpUnusedLocalVariableInspection */
			$result = $this->repo
				->findOne()
				->with('id', Operator::EQUAL, 0)
				->getItem();
			$this->fail('Expected EntityNotFoundException not thrown');
		}
		catch (\Exception $e)
		{
			$this->assertTrue($e instanceof EntityNotFoundException);
		}
	}

	/**
	 * @testdox Collection finder returns arrays on requested columns
	 */
	public function testCollectionFinderReturnsArrayOnRequestedColumns()
	{
		$result = $this->repo
			->findAll()
			->columns(['*'])
			->with('id', Operator::EQUAL, 1)
			->getItems();

		$this->assertEquals(1, count($result));
		$this->assertTrue(is_array($result[0]));
	}

	/**
	 * @testdox Collection finder returns only requested columns
	 */
	public function testCollectionFinderReturnsRequestedColumns()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::EQUAL, 1)
			->getItems();

		$this->assertEquals([['id' => 1, 'title' => 'First Article']], $result);
	}

	/**
	 * @testdox Collection finder returns an Collection, if no columns are requested
	 */
	public function testCollectionFinderReturnsCollectionIfNoColumnsSpecified()
	{
		$result = $this->repo
			->findAll()
			->with('id', Operator::EQUAL, 1)
			->getItems();

		$this->assertTrue($result[0] instanceof Article);
	}

	/**
	 * @testdox Collection finder returns empty array, if no result is found
	 */
	public function testCollectionFinderReturnsEmptyArrayIfNoResult()
	{
		$result = $this->repo
			->findAll()
			->with('id', Operator::EQUAL, 0)
			->getItems();

		$this->assertEquals([], $result);
	}

	/**
	 * @testdox Result set can be ordered
	 */
	public function testResultSetCanBeOrdered()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->getItems();

		$this->assertEquals(print_r(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 2, 'title' => 'Second Article'],
				['id' => 3, 'title' => 'Part One'],
				['id' => 4, 'title' => 'Part Two'],
			], true),
			print_r($result, true)
		);

		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->orderBy('id', 'DESC')
			->getItems();

		$this->assertEquals(
			[
				['id' => 4, 'title' => 'Part Two'],
				['id' => 3, 'title' => 'Part One'],
				['id' => 2, 'title' => 'Second Article'],
				['id' => 1, 'title' => 'First Article'],
			],
			$result
		);

		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->orderBy('title')
			->getItems();

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 3, 'title' => 'Part One'],
				['id' => 4, 'title' => 'Part Two'],
				['id' => 2, 'title' => 'Second Article'],
			],
			$result
		);
	}

	/**
	 * @testdox Result set can be sliced without explicit start
	 */
	public function testResultSetCanBeSlicedWithoutStart()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->getItems(2);

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 2, 'title' => 'Second Article'],
			],
			$result
		);
	}

	/**
	 * @testdox Result set can be sliced
	 */
	public function testResultSetCanBeSliced()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->getItems(2, 1);

		$this->assertEquals(
			[
				['id' => 2, 'title' => 'Second Article'],
				['id' => 3, 'title' => 'Part One'],
			],
			$result
		);
	}

	public function testSupportsEqualOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::EQUAL, 1)
			->getItems();

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
			],
			$result
		);
	}

	public function testSupportsNotEqualOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::NOT_EQUAL, 1)
			->getItems();

		$this->assertEquals(
			[
				['id' => 2, 'title' => 'Second Article'],
				['id' => 3, 'title' => 'Part One'],
				['id' => 4, 'title' => 'Part Two'],
			],
			$result
		);
	}

	public function testSupportsGreaterThanOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::GREATER_THAN, 2)
			->getItems();

		$this->assertEquals(
			[
				['id' => 3, 'title' => 'Part One'],
				['id' => 4, 'title' => 'Part Two'],
			],
			$result
		);
	}

	public function testSupportsGreaterThanOrEqualOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::GREATER_OR_EQUAL, 2)
			->getItems();

		$this->assertEquals(
			[
				['id' => 2, 'title' => 'Second Article'],
				['id' => 3, 'title' => 'Part One'],
				['id' => 4, 'title' => 'Part Two'],
			],
			$result
		);
	}

	public function testSupportsLessThanOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::LESS_THAN, 3)
			->getItems();

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 2, 'title' => 'Second Article'],
			],
			$result
		);
	}

	public function testSupportsLessThanOrEqualOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::LESS_OR_EQUAL, 3)
			->getItems();

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 2, 'title' => 'Second Article'],
				['id' => 3, 'title' => 'Part One'],
			],
			$result
		);
	}

	public function testSupportsContainsOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('title', Operator::CONTAINS, 'Article')
			->getItems();

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 2, 'title' => 'Second Article'],
			],
			$result
		);
	}

	public function testSupportsStartsWithOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('title', Operator::STARTS_WITH, 'Part')
			->getItems();

		$this->assertEquals(
			[
				['id' => 3, 'title' => 'Part One'],
				['id' => 4, 'title' => 'Part Two'],
			],
			$result
		);
	}

	public function testSupportsEndsWithOperator()
	{
		$result = $this->repo->findAll()
		                     ->columns(['id', 'title'])
		                     ->with('title', Operator::ENDS_WITH, 'Article')
		                     ->getItems();

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 2, 'title' => 'Second Article'],
			],
			$result
		);
	}

	public function testSupportsMatchesOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('title', Operator::MATCHES, 'rt\\s')
			->getItems();

		$this->assertEquals(
			[
				['id' => 3, 'title' => 'Part One'],
				['id' => 4, 'title' => 'Part Two'],
			],
			$result
		);
	}

	public function testSupportsInOperator()
	{
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', Operator::IN, [1, 3])
			->getItems();

		$this->assertEquals(
			[
				['id' => 1, 'title' => 'First Article'],
				['id' => 3, 'title' => 'Part One'],
			],
			$result
		);
	}

	/**
	 * @expectedException \Joomla\ORM\Exception\InvalidOperatorException
	 */
	public function testThrowsExceptionOnIllegalOperator()
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		$result = $this->repo
			->findAll()
			->columns(['id', 'title'])
			->with('id', 'NONEXISTANT', '1')
			->getItems();
	}

	public function testStoreNew()
	{
		$article           = new Article;
		$article->title    = "New Article";
		$article->teaser   = "This is a new article";
		$article->body     = "It serves test purposes only and should go away afterwards.";
		$article->author   = __METHOD__;
		$article->license  = 'CC';
		$article->parentId = 0;

		$this->repo->add($article);

		$this->repo->commit();

		$this->assertNotEmpty($article->id);

		$loaded = $this->repo->getById($article->id);

		$this->assertSame($article, $loaded);

		return $article->id;
	}

	/**
	 * @depends testStoreNew
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function testStoreUpdate($id)
	{
		$article = $this->repo->getById($id);

		$this->assertInstanceOf(Article::class, $article);

		$article->title  = "Changed Article";
		$article->teaser = "This article was changed";
		$article->author = __METHOD__;

		$this->repo->commit();

		$loaded = $this->repo->getById($id);

		$this->assertSame($article, $loaded);

		return $id;
	}

	/**
	 * @depends testStoreUpdate
	 *
	 * @param int $id
	 *
	 * @return int
	 */
	public function testDelete($id)
	{
		$article = $this->repo->getById($id);

		$this->repo->remove($article);

		$this->repo->commit();

		$this->expectException(EntityNotFoundException::class);
		/** @noinspection PhpUnusedLocalVariableInspection */
		$loaded = $this->repo->getById($id);
	}

	/**
	 * @expectedException \Joomla\ORM\Exception\OrmException
	 */
	public function testDeleteWrongId()
	{
		$article          = new Article;
		$article->id      = PHP_INT_MAX;
		$article->title   = "Non-existant Article";
		$article->teaser  = "This article is not existant, but has an id";
		$article->body    = "It serves test purposes only and should never go into the database.";
		$article->author  = __METHOD__;
		$article->license = 'CC';
		/** @noinspection PhpUndefinedFieldInspection */
		$article->parent_id = 0;

		$this->repo->remove($article);

		$this->repo->commit();
	}

	/**
	 * @expectedException \Joomla\ORM\Exception\OrmException
	 */
	public function testDeleteEmptyId()
	{
		$article          = new Article;
		$article->title   = "Non-existant Article";
		$article->teaser  = "This article is not existant and has no id";
		$article->body    = "It serves test purposes only and should never go into the database.";
		$article->author  = __METHOD__;
		$article->license = 'CC';
		/** @noinspection PhpUndefinedFieldInspection */
		$article->parent_id = 0;

		$this->repo->remove($article);

		$this->repo->commit();
	}

	public function testParentRelation()
	{
		$article = $this->repo->getById(2);
		$this->assertInstanceOf(RepositoryInterface::class, $article->children);

		$children = $article->children->getAll();

		$this->assertEquals(2, count($children));
	}
}
