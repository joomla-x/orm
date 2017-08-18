<?php
/**
 * Part of the Joomla Framework ORM Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\ORM\Filter;

use JFactory;
use Joomla\ORM\Operator;
use Joomla\ORM\Storage\CollectionFinderInterface;
use Joomla\Registry\Registry;

class Filter
{
    /**
     * @var Registry
     */
    private $state;

    /**
     * Filter constructor.
     *
     * @param Registry $state
     */
    public function __construct(Registry $state)
    {
        $this->state = $state;
    }

    /**
     * @param CollectionFinderInterface $finder
     * @param string                    $fieldName
     *
     * @return CollectionFinderInterface
     */
    public function applyTimeRange($finder, $fieldName)
    {
        $dateTime = $this->state->get("filter.{$fieldName}_from_dateformat");
        $dateTime = (!empty($dateTime)) ? $this->isValidDate($dateTime) : null;

        if ($dateTime != null) {
            $finder = $finder->with($fieldName, Operator::GREATER_OR_EQUAL, $dateTime);
        }

        $dateTime = $this->state->get("filter.{$fieldName}_to_dateformat");
        $dateTime = (!empty($dateTime)) ? $this->isValidDate($dateTime) : null;

        if ($dateTime != null) {
            $finder = $finder->with($fieldName, Operator::LESS_OR_EQUAL, $dateTime);
        }

        return $finder;
    }

    /**
     * @param CollectionFinderInterface $finder
     * @param string                    $fieldName
     *
     * @return CollectionFinderInterface
     */
    public function applyTitleSearch($finder, $fieldName = 'name')
    {
        $search = $this->state->get('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $finder = $finder->with('id', Operator::EQUAL, (int)substr($search, 3));
            } else {
                $finder = $finder->with($fieldName, Operator::CONTAINS, $search);
            }
        }

        return $finder;
    }

    /**
     * @param CollectionFinderInterface $finder
     * @param string                    $fieldName
     *
     * @return CollectionFinderInterface
     */
    public function applyStateFilter($finder, $fieldName = 'state')
    {
        if (!JFactory::getUser()->authorise('core.edit', 'com_patients')) {
            $finder = $finder->with($fieldName, Operator::EQUAL, 1);
        }

        return $finder;
    }

    /**
     * @param CollectionFinderInterface $finder
     * @param string                    $fieldName
     *
     * @return CollectionFinderInterface
     */
    public function applySimpleFilter(CollectionFinderInterface $finder, $fieldName)
    {
        $filterValue = $this->state->get("filter." . $fieldName, null);
        if (!empty($filterValue)) {
            $finder = $finder->with($fieldName, Operator::EQUAL, $filterValue);
        }

        return $finder;
    }

    /**
     * @param CollectionFinderInterface $finder
     * @param string                    $fieldName
     *
     * @return CollectionFinderInterface
     */
    public function applyContainFilter(CollectionFinderInterface $finder, $fieldName)
    {
        $filterValue = $this->state->get("filter." . $fieldName, null);
        if (!empty($filterValue)) {
            $finder = $finder->with($fieldName, Operator::CONTAINS, $filterValue);
        }

        return $finder;
    }

    /**
     * @param CollectionFinderInterface $finder
     * @param string                    $fieldName
     *
     * @return CollectionFinderInterface
     */
    public function applyOrdering($finder, $fieldName = 'ordering')
    {
        $orderCol  = $this->state->get('list.ordering', $fieldName);
        $orderDirn = $this->state->get('list.direction', 'asc');

        if (!empty($orderCol)) {
            $finder = $finder->orderBy($orderCol, $orderDirn);
        }

        return $finder;
    }

    /**
     * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
     *
     * @param   string $date Date to be checked
     *
     * @return bool
     */
    private function isValidDate($date)
    {
        $date = str_replace('/', '-', $date);

        return (date_create($date)) ? JFactory::getDate($date)->format("Y-m-d") : null;
    }
}
