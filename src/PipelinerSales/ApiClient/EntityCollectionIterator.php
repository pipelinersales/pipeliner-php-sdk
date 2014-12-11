<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient;

use PipelinerSales\ApiClient\Repository\RepositoryInterface;

/**
 * Class for conveniently iterating through collections of entities beyond the
 * ones which have been loaded from the server.
 *
 * Represents a range 0-n, where n is the number of entities that the query
 * would have returned if no limit was set. Only a part of this range is loaded
 * at a time, so iterating may cause additional HTTP requests to be issued.
 */
class EntityCollectionIterator implements \SeekableIterator
{
    /** @var Query\Criteria  */
    private $criteria;
    private $repository;
    private $collection;
    private $position;

    /**
     * @param RepositoryInterface $repository repository for loading additional entities
     * @param EntityCollection $collection initial collection of already loaded entities
     */
    public function __construct(RepositoryInterface $repository, EntityCollection $collection)
    {
        $this->repository = $repository;
        $this->collection = $collection;

        $this->criteria = $this->collection->getCriteriaCopy();
        $this->position = $this->criteria->getOffset(true);
    }

    /**
     * @return Entity
     */
    public function current()
    {
        if ($this->dataAvailable()) {
            return $this->collection->offsetGet($this->position - $this->collection->getStartIndex());
        } else {
            $this->criteria->offset($this->position);
            $this->collection = $this->repository->get($this->criteria);
            return $this->collection->offsetGet($this->position - $this->collection->getStartIndex());
        }
    }

    /**
     * @return integer
     */
    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return ($this->position >= 0 and $this->position < $this->collection->getTotalCount());
    }

    /**
     * True if data for the current position has been loaded from the server and is
     * locally available.
     * @return boolean
     */
    public function dataAvailable()
    {
        return ($this->position >= $this->collection->getStartIndex() and
                $this->position <= $this->collection->getEndIndex());
    }

    /**
     * True if data for the next position has been loaded from the server and is
     * locally available. This is useful in foreach loops to determine whether the
     * next iteration will issue a HTTP request.
     * @return boolean
     */
    public function nextDataAvailable()
    {
        return ($this->position+1 >= $this->collection->getStartIndex() and
                $this->position+1 <= $this->collection->getEndIndex());
    }

    /**
     * True if the current position is the last position within the range.
     * @return boolean
     */
    public function atEnd()
    {
        return ($this->position == $this->collection->getTotalCount()-1);
    }

    public function seek($position)
    {
        $this->position = $position;
    }
}
