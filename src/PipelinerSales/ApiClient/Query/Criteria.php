<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Query;

use PipelinerSales\ApiClient\Defaults;

/**
 * Represents the query parameters for loading entities.
 *
 * For details, see the {@link
 * http://workspace.pipelinersales.com/community/api/data/Querying_rest.html
 * API documentation on querying}.
 *
 * @method static Criteria limit($limit) Limits how many entities to load. Use the NO_LIMIT constant to disable limits.
 * @method static Criteria offset($offset)
 * @method static Criteria sort($sort) Set sorting of the result, either by using a raw string (see API documentation) or a Sort object
 * @method static Criteria filter($filter) Only return entities which conform to the specified filter - either a raw string (see API documentation) or a Filter object
 * @method static Criteria after($after) Only load entities modified after the date, specified as either a string or a DateTime object
 * @method static Criteria loadonly($loadonly) Only load the specified fields, specified as either an array or a string separated with |
 */
class Criteria
{

    private static $properties = array(
        'limit' => 'setLimit',
        'offset' => 'setOffset',
        'sort' => 'setSort',
        'filter' => 'setFilter',
        'after' => 'setAfter',
        'loadonly' => 'setLoadOnly'
    );
    private $limit = null;
    private $offset = null;
    private $sort = null;
    private $filter = null;
    private $after = null;
    private $loadonly = null;
    private $dateTimeFormat;
    private $defaultLimit;

    /**
     * Disable the limit on how many entities to load. Please use with caution.
     */
    const NO_LIMIT = -1;

    /**
     * Creates a new set of criteria.
     * @param mixed $criteria initial criteria to use, see {@see set}
     * @param string $dateTimeFormat format to use for converting DateTime objects
     */
    public function __construct($criteria = array(), $dateTimeFormat = Defaults::DATE_FORMAT, $defaultLimit = Defaults::DEFAULT_LIMIT)
    {
        $this->dateTimeFormat = $dateTimeFormat;
        $this->defaultLimit = $defaultLimit;
        $this->set($criteria);
    }

    /**
     * Returns the resulting url-encoded query string.
     * @return string
     */
    public function toUrlQuery()
    {
        $queryData = array();
        foreach (self::$properties as $p => $setter) {
            if ($this->$p !== null) {
                $queryData[$p] = $this->$p;
            }
        }
        return http_build_query($queryData);
    }

    /**
     * Returns the currently set limit. Null will be returned if the limit is not set.
     * @return integer|null
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Returns the currently set limit, or the default limit if none is set.
     * @return integer
     */
    public function getEffectiveLimit()
    {
        if ($this->limit === null) {
            return $this->defaultLimit;
        }
        return $this->limit;
    }

    /**
     * @param integer $limit
     * @return Criteria
     */
    private function setLimit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Returns the currently set offset. Null will be returned if no offset is set.
     * @return integer|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Returns the currently set offset, or the default offset 0 if no offset is set.
     * @return integer
     */
    public function getEffectiveOffset()
    {
        if ($this->offset === null) {
            return 0;
        }
        return $this->getOffset();
    }

    /**
     * @param integer $offset
     * @return Criteria
     */
    private function setOffset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param string|Sort
     * @return Criteria
     */
    public function setSort($sort)
    {
        if ($sort instanceof Sort) {
            $this->sort = $sort->getString();
        } else {
            $this->sort = $sort;
        }
        return $this;
    }

    /**
     * Returns the currently set filter string.
     * @return string|null
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param Filter|string
     * @return Criteria
     */
    private function setFilter($filter)
    {
        if ($filter instanceof Filter) {
            $this->filter = $filter->getString();
        } else {
            $this->filter = $filter;
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAfter()
    {
        return $this->after;
    }

    /**
     * @param string|\DateTime $after
     * @return Criteria
     */
    private function setAfter($after)
    {
        if (is_string($after)) {
            $this->after = $after;
        } elseif ($after instanceof \DateTime) {
            $afterCopy = clone $after;
            $afterCopy->setTimezone(new \DateTimeZone('UTC'));
            $this->after = $afterCopy->format($this->dateTimeFormat);
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLoadOnly()
    {
        return $this->loadonly;
    }

    /**
     * @param string|array
     * @return Criteria
     */
    private function setLoadOnly($loadonly)
    {
        if (is_string($loadonly)) {
            $this->loadonly = $loadonly;
        } elseif (is_array($loadonly)) {
            $this->loadonly = implode('|', $loadonly);
        }
        return $this;
    }

    /**
     * Sets multiple parameters at once.
     *
     * @param mixed $criteria Can be one of the following:
     * <ul>
     * <li>Another Criteria object - copies that object's criteria into this one.
     * Competely replaces the current object's values, unsetting those which have not been
     * set in $criteria</li>
     * <li>An array - sets the parameters present in the array. Parameters not present
     * in the array will <b>not</b> be unset. If you wish to unset certain parameters,
     * set them to null in the array.</li>
     * <li>A string - the resulting query string (without a leading question mark),
     * will be parsed back into the individual parameters and processed like an array.</li>
     * </ul>
     */
    public function set($criteria)
    {
        if ($criteria instanceof Criteria) {
            //this is much faster than using the same method that's used for arrays (see below)
            $this->limit = $criteria->limit;
            $this->offset = $criteria->offset;
            $this->sort = $criteria->sort;
            $this->filter = $criteria->filter;
            $this->after = $criteria->after;
            $this->loadonly = $criteria->loadonly;
        } elseif (is_string($criteria)) {
            //parse the string into an array and then parse the array
            $criteria = $this->parseHttpQuery($criteria);
        }

        if (is_array($criteria)) {
            foreach (self::$properties as $p => $setter) {
                if (isset($criteria[$p])) {
                    $this->$setter($criteria[$p]);
                }
            }
        }
    }

    /**
     * Parses a http query into a key-value array, similar to PHP's parse_str function.
     * @param string $query query string (without a leading question mark)
     * @return array
     */
    private function parseHttpQuery($query)
    {
        /* can't use parse_str, because the minimum supported PHP version
         * is 5.3, which still has the magic_quotes_gpc option that affects its
         * result, so some users could hypothetically have that enabled */
        if (empty($query)) {
            return array();
        }

        $result = array();
        $parts = explode('&', $query);
        foreach ($parts as $p) {
            $keyval = explode('=', $p);
            $result[urldecode($keyval[0])] = urldecode($keyval[1]);
        }
        return $result;
    }

    public static function __callStatic($name, $arguments)
    {
        if (isset(self::$properties[$name])) {
            return new Criteria(array( $name => $arguments[0] ));
        }
        throw new \BadMethodCallException('Call to a non-existent static method \'' . $name . '\'');
    }

    public function __call($name, $arguments)
    {
        if (isset(self::$properties[$name])) {
            $setter = self::$properties[$name];
            $this->$setter($arguments[0]);
            return $this;
        }
        throw new \BadMethodCallException('Call to a non-existent method \'' . $name . '\'');
    }
}
