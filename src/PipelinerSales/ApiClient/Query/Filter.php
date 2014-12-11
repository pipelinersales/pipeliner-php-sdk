<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Query;

use PipelinerSales\ApiClient\Defaults;
use PipelinerSales\ApiClient\PipelinerClientException;

/**
 * A conventient wrapper for building the filter string used in queries.
 *
 * For details, see the {@link
 * http://workspace.pipelinersales.com/community/api/data/Querying_rest.html
 * API documentation on querying}.
 *
 * All the magic methods can also be called statically, e.g.
 * <code>
 * Filter::equals("NAME", "Joe")->greaterThan("HEIGHT", 0)
 * </code>
 *
 * For most operators (all except ll, rl, fl and their aliases), DateTime objects can be
 * used as value.
 *
 * @method static Filter eq(string $fieldName, mixed $value) field <b>equals</b> value
 * @method static Filter equals(string $fieldName, mixed $value) alias for {@see eq}
 * @method static Filter ne(string $fieldName, mixed $value) field <b>does not equal</b> value
 * @method static Filter doesNotEqual(string $fieldName, mixed $value) alias for {@see ne}
 * @method static Filter gt(string $fieldName, mixed $value) field is <b>greater than</b> value
 * @method static Filter greaterThan(string $fieldName, mixed $value) alias for {@see gt}
 * @method static Filter lt(string $fieldName, mixed $value) field is <b>less than</b> value
 * @method static Filter lessThan(string $fieldName, mixed $value) alias for {@see lt}
 * @method static Filter ge(string $fieldName, mixed $value) field is <b>greater or equal</b> to value
 * @method static Filter gte(string $fieldName, mixed $value) alias for {@see ge}
 * @method static Filter greaterOrEqual(string $fieldName, mixed $value) alias for {@see ge}
 * @method static Filter le(string $fieldName, mixed $value) field is <b>less or equal</b> to value
 * @method static Filter lte(string $fieldName, mixed $value) alias for {@see le}
 * @method static Filter lessOrEqual(string $fieldName, mixed $value) alias for {@see le}
 * @method static Filter ll(string $fieldName, string $value) field starts with value
 * @method static Filter startsWith(string $fieldName, string $value) alias for {@see ll}
 * @method static Filter rl(string $fieldName, string $value) field ends with value
 * @method static Filter endsWith(string $fieldName, string $value) alias for {@see rl}
 * @method static Filter fl(string $fieldName, string $value) field contains value
 * @method static Filter contains(string $fieldName, string $value) alias for {@see fl}
 * @method static Filter raw(string $filterString) appends a separator &#40;|&#41; followed
 * by the raw filter string to the current filter string
 */
class Filter
{

    private $filterString;
    private $dateTimeFormat;
    private static $operators = array(
        'eq' => 'eq',
        'equals' => 'eq',
        'ne' => 'ne',
        'doesNotEqual' => 'ne',
        'gt' => 'gt',
        'greaterThan' => 'gt',
        'lt' => 'lt',
        'lessThan' => 'lt',
        'ge' => 'ge',
        'gte' => 'ge',
        'greaterOrEqual' => 'ge',
        'le' => 'le',
        'lte' => 'le',
        'lessOrEqual' => 'le',
        'll' => 'll',
        'startsWith' => 'll',
        'rl' => 'rl',
        'endsWith', 'rl',
        'fl' => 'fl',
        'contains' => 'fl',
        'raw' => 'raw'
    );

    public function __construct($filter = '', $dateTimeFormat = Defaults::DATE_FORMAT)
    {
        if ($filter instanceof Filter) {
            $this->filterString = $filter->filterString;
        } else {
            $this->filterString = $filter;
        }
    }

    public function __call($name, $arguments)
    {
        if (isset(self::$operators[$name])) {
            if (!empty($this->filterString)) {
                $this->filterString .= '|';
            }

            if (self::$operators[$name] == 'raw') {
                $this->filterString .= $arguments[0];
            } else {
                if ($arguments[1] instanceof \DateTime) {
                    $dateTimeCopy = clone $arguments[1];
                    $dateTimeCopy->setTimezone(new \DateTimeZone('UTC'));
                    $arguments[1] = $dateTimeCopy->format($this->dateTimeFormat);
                }

                $this->filterString .= $arguments[0] . '::' . $arguments[1];
                if (self::$operators[$name] != 'eq') {
                    $this->filterString .= '::' . self::$operators[$name];
                }
            }

            return $this;
        } else {
            throw new PipelinerClientException('Invalid filter operator: ' . $name);
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if (isset(self::$operators[$name])) {
            $filter = new Filter();
            $filter->$name($arguments[0], $arguments[1]);
            return $filter;
        } else {
            throw new PipelinerClientException('Invalid filter operator: ' . $name);
        }
    }

    /**
     * Returns the resulting filter string usable in query
     * @return string
     */
    public function getString()
    {
        return $this->filterString;
    }
}
