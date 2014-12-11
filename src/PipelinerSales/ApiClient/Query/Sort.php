<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Query;

/**
 * A convenient wrapper for generating the sort string for queries.
 *
 * For details, see the {@link http://workspace.pipelinersales.com/community/api/data/Querying_rest.html
 * API documentation}.
 *
 * All the magic methods can also be called statically, e.g.
 * <code>
 * Sort::asc('NAME')->desc('MODIFIED')
 * </code>
 *
 * @method static Sort asc(string $fieldName) sorts by a field in ascending order
 * @method static Sort desc(string $fieldName) sorts by a field in descending order
 * @method static Sort raw(string $sortString) appends a separator &#40;|&#41; followed
 * by a raw string to the current sort string
 */
class Sort
{

    private $sortString = '';

    /**
     * Constructor, optionally copies an existing sort string into this object.
     * @param mixed $sort either a sort string or another Sort object
     */
    public function __construct($sort = '')
    {
        if ($sort instanceof Sort) {
            $this->sortString = $sort->sortString;
        } else {
            $this->raw($sort);
        }
    }

    public function __call($name, $arguments)
    {
        if ($name === 'asc' or $name === 'desc' or $name === 'raw') {
            if (!empty($this->sortString)) {
                $this->sortString .= '|';
            }

            if ($name === 'desc') {
                $this->sortString .= '-';
            }

            $this->sortString .= $arguments[0];
            return $this;
        }
        throw new \BadMethodCallException('Call to a non-existent method \'' . $name . '\'');
    }

    public static function __callStatic($name, $arguments)
    {
        if ($name === 'asc' or $name === 'desc' or $name === 'raw') {
            $sort = new Sort();
            $sort->$name($arguments[0]);
            return $sort;
        }
        throw new \BadMethodCallException('Call to a non-existent static method \'' . $name . '\'');
    }

    /**
     * Returns the resulting sort string.
     * @return string
     */
    public function getString()
    {
        return $this->sortString;
    }
}
