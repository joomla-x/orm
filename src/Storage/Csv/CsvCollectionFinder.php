<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Storage\Csv;

use Joomla\ORM\Entity\EntityBuilder;
use Joomla\ORM\Entity\EntityRegistry;
use Joomla\ORM\Exception\InvalidOperatorException;
use Joomla\ORM\Operator;
use Joomla\ORM\Storage\CollectionFinderInterface;

/**
 * Class CsvCollectionFinder
 *
 * @package  Joomla/ORM
 *
 * @since    __DEPLOY_VERSION__
 */
class CsvCollectionFinder implements CollectionFinderInterface
{
	/** @var CsvDataGateway */
	private $gateway;

	/** @var string  Class of the entity */
	private $entityClass;

	/** @var  string  Name of the data table */
	private $table;

	/** @var  EntityRegistry */
	private $entityRegistry;

	/** @var  array  Conditions */
	private $conditions = [];

	/** @var  array  Ordering instructions */
	private $ordering = [];

	/** @var array */
	private $columns = [];

	/**
	 * CsvCollectionFinder constructor.
	 *
	 * @param   CsvDataGateway $gateway        The data gateway
	 * @param   string         $tableName      The table name
	 * @param   string         $entityClass    The class name of the entity
	 * @param   EntityRegistry $entityRegistry The entity registry
	 */
	public function __construct(CsvDataGateway $gateway, $tableName, $entityClass, EntityRegistry $entityRegistry)
	{
		$this->gateway        = $gateway;
		$this->entityClass    = $entityClass;
		$this->table          = $tableName;
		$this->entityRegistry = $entityRegistry;
	}

	/**
	 * Set the ordering.
	 *
	 * @param   string $column    The name of the ordering column
	 * @param   string $direction One of 'ASC' (ascending) or 'DESC' (descending)
	 *
	 * @return  $this
	 */
	public function orderBy($column, $direction = 'ASC')
	{
		$orderVal         = [
			'ASC'  => 1,
			'DESC' => -1
		];
		$this->ordering[] = [
			'column'    => $column,
			'direction' => $orderVal[strtoupper($direction)]
		];

		return $this;
	}

	/**
	 * Fetch the entities
	 *
	 * @param   int $count The number of matching entities to retrieve
	 * @param   int $start The index of the first entity to retrieve
	 *
	 * @return  array
	 */
	public function getItems($count = null, $start = 0)
	{
		$matches = $this->gateway->getAll($this->table);
		$matches = $this->applyConditions($matches);
		$matches = $this->applyOrdering($matches);

		if (!empty($this->columns))
		{
			$result = $this->applyColumns($matches);
		}
		else
		{
			$result = $this->castToEntity($matches);
		}

		return array_slice($result, $start, $count);
	}

	/**
	 * Apply the conditions
	 *
	 * @param   array $matches The records
	 *
	 * @return  array
	 */
	private function applyConditions($matches)
	{
		foreach ($this->conditions as $condition)
		{
			$matches = $this->filter($matches, $condition);
		}

		return $matches;
	}

	/**
	 * Apply a condition to a record set
	 *
	 * @param   array $rows      The data rows
	 * @param   array $condition The filter condition
	 *
	 * @return array
	 */
	protected function filter($rows, $condition)
	{
		$result = [];

		foreach ((array) $rows as $row)
		{
			$actual = isset($row[$condition['field']]) ? $row[$condition['field']] : null;

			switch ($condition['op'])
			{
				case Operator::EQUAL:
					if ($actual == $condition['value'])
					{
						$result[] = $row;
					}
					break;

				case Operator::NOT_EQUAL:
					if ($actual != $condition['value'])
					{
						$result[] = $row;
					}
					break;

				case Operator::GREATER_THAN:
					if ($actual > $condition['value'])
					{
						$result[] = $row;
					}
					break;

				case Operator::GREATER_OR_EQUAL:
					if ($actual >= $condition['value'])
					{
						$result[] = $row;
					}
					break;

				case Operator::LESS_THAN:
					if ($actual < $condition['value'])
					{
						$result[] = $row;
					}
					break;

				case Operator::LESS_OR_EQUAL:
					if ($actual <= $condition['value'])
					{
						$result[] = $row;
					}
					break;

				case Operator::CONTAINS:
					if (strpos($actual, $condition['value']) !== false)
					{
						$result[] = $row;
					}
					break;

				case Operator::STARTS_WITH:
					if (preg_match('~^' . preg_quote($condition['value'], '~') . '~', $actual))
					{
						$result[] = $row;
					}
					break;

				case Operator::ENDS_WITH:
					if (preg_match('~' . preg_quote($condition['value'], '$~') . '~', $actual))
					{
						$result[] = $row;
					}
					break;

				case Operator::MATCHES:
					if (preg_match('~' . str_replace('~', '\~', $condition['value']) . '~', $actual))
					{
						$result[] = $row;
					}
					break;

				case Operator::IN:
					if (in_array($actual, $condition['value']))
					{
						$result[] = $row;
					}
					break;

				default:
					throw new InvalidOperatorException;
					break;
			}
		}

		return $result;
	}

	/**
	 * Apply the ordering
	 *
	 * @param   array $matches The records
	 *
	 * @return  array
	 */
	private function applyOrdering($matches)
	{
		foreach ($this->ordering as $ordering)
		{
			usort(
				$matches,
				function ($aRow, $bRow) use ($ordering) {
					$a = $aRow[$ordering['column']];
					$b = $bRow[$ordering['column']];

					return $ordering['direction'] * ($a == $b ? 0 : ($a < $b ? -1 : 1));
				}
			);
		}

		return $matches;
	}

	/**
	 * Apply the columns
	 *
	 * @param   array $matches The records
	 *
	 * @return  array
	 */
	private function applyColumns($matches)
	{
		if (empty($matches) || empty($this->columns) || in_array('*', $this->columns))
		{
			return $matches;
		}

		$availableColumns = array_keys(reset($matches));
		$requestedColumns = $this->columns;

		if (count($this->columns) == 1 && $this->columns[0] != '*')
		{
			$result = [];

			foreach ($matches as $match)
			{
				$result[] = $match[$this->columns[0]];
			}

			return $result;
		}

		foreach (array_diff($availableColumns, $this->columns) as $remove)
		{
			foreach ($matches as &$match)
			{
				unset($match[$remove]);
			}
		}

		return $matches;
	}

	/**
	 * Cast array to entity
	 *
	 * @param   array $matches The records
	 *
	 * @return  array
	 */
	private function castToEntity($matches)
	{
		$entities = $this->entityRegistry->getEntityBuilder()->castToEntity($matches, $this->entityClass);

		return $entities;
	}

	/**
	 * Define the columns to be retrieved.
	 *
	 * @param   array $columns The column names
	 *
	 * @return  $this
	 */
	public function columns($columns)
	{
		if (!is_array($columns))
		{
			$columns = preg_split('~\s*,\s*~', trim($columns));
		}

		$this->columns = $columns;

		return $this;
	}

	/**
	 * Define a condition.
	 *
	 * @param   mixed  $lValue The left value for the comparision
	 * @param   string $op     The comparision operator, one of the \Joomla\ORM\Finder\Operator constants
	 * @param   mixed  $rValue The right value for the comparision
	 *
	 * @return  $this
	 */
	public function with($lValue, $op, $rValue)
	{
		$this->conditions[] = [
			'field' => $lValue,
			'op'    => $op,
			'value' => $rValue
		];

		return $this;
	}
}
