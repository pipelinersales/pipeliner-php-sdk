<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient;

use PipelinerSales\ApiClient\Query\Criteria;

/**
 * Represents an immutable collection of multiple entities of a common type. The
 * entities themselves are mutable (i.e. you can modify the entity object inside
 * the collection, but you can not add/remove/reorder/etc. entities from/to the collection.
 *
 * Works like a regular PHP array, but contains additional information about the
 * number of entities that were not loaded due to a query limit.
 *
 * @method Entity[] getArrayCopy
 */
class EntityCollection extends \ArrayObject
{

    private $criteria;
    private $totalCount;
    private $startIndex;
    private $endIndex;
    private static $immutableError = 'EntityCollection is immutable';

    /**
     * Creates a collection based on an existing array
     *
     * @param array $data
     * @param mixed $criteria Criteria that was used when querying for this colleciton. Any type
     * that the can be passed to the constructor of {@see Criteria} can be used here. A copy of this
     * argument will be made, so it is safe to pass a Criteria object - it will not be inadvertently
     * modified at a later time.
     * @param integer $startIndex the offset used in the query
     * @param integer $endIndex the offset of the last loaded entity
     * @param integer $totalCount total count of all entities that match the query
     * @throws PipelinerClientException when the number of items in the array doesn't match the specified indexes
     */
    public function __construct($data, $criteria, $startIndex, $endIndex, $totalCount)
    {
        parent::__construct($data);

        //sanity check
        if ($endIndex - $startIndex + 1 != $this->count()) {
            //empty collections are a special case - Pipeliner's server currently returns 0 - -1 range in such a case
            if ($endIndex != -1) {
                throw new
                PipelinerClientException('Returned content range doesn\'t match the number of returned entities');
            }
        }

        $this->criteria = new Criteria($criteria);
        $this->startIndex = $startIndex;
        $this->totalCount = $totalCount;
        $this->endIndex = $endIndex;
    }

    /**
     * Returns the number of entities available on the server (ignoring the limit).
     * @return integer
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Returns a copy of the criteria that was used to fetch this collection.
     * @return Criteria
     */
    public function getCriteriaCopy()
    {
        return clone $this->criteria;
    }

    /**
     * Returns the index of the first item in this colleciton. It is
     * identical to the offset that was used to fetch this collection.
     * @return integer
     */
    public function getStartIndex()
    {
        return $this->startIndex;
    }

    /**
     * Returns the index of the last item in this collection.
     * @return integer
     */
    public function getEndIndex()
    {
        return $this->endIndex;
    }

    /** @ignore */
    public function offsetSet($index, $newval)
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function offsetUnset($index)
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function exchangeArray($input)
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function asort()
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function natsort()
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function append($value)
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function natcasesort()
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function uasort($cmp_function)
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function uksort($cmp_function)
    {
        throw new PipelinerClientException(self::$immutableError);
    }

    /** @ignore */
    public function ksort()
    {
        throw new PipelinerClientException(self::$immutableError);
    }
}
