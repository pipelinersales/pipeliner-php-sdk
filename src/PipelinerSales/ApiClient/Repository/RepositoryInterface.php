<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Repository;

use PipelinerSales\ApiClient\Entity;
use PipelinerSales\ApiClient\EntityCollection;
use PipelinerSales\ApiClient\EntityCollectionIterator;
use PipelinerSales\ApiClient\Http\PipelinerHttpException;
use PipelinerSales\ApiClient\Http\Response;
use PipelinerSales\ApiClient\Http\CreatedResponse;
use PipelinerSales\ApiClient\PipelinerClientException;

/**
 * An interface for retrieving and manipulating entities.
 */
interface RepositoryInterface
{

    // flags for batch operations:
    
    /** If any error occurs, no entity will be processed, and the entire batch will be rolled back. */
    const FLAG_ROLLBACK_ON_ERROR = 0;

    /** If an error occurs for an entity, the entity is ignored and the system continues with the next one. */
    const FLAG_IGNORE_ON_ERROR = 1;

    /** If any error occurs during the update of an entity (e.g. the entity doesn’t exists),
     *  the entity will be inserted instead, and a new unique identificator will be generated */
    const FLAG_INSERT_ON_UPDATE = 2;

    /** The method will return a list of IDs which cannot be deleted.
     * Can be used with combination with FLAG_IGNORE_ON_ERROR. */
    const FLAG_GET_NO_DELETED_ID = 4;

    /** If any error occurs for an entity, the entity is ignored and the system continues with the next one. */
    const FLAG_IGNORE_AND_RETURN_ERRORS = 8;

    /** Only updated fields in the entity will be validated instead of all fields. */
    const FLAG_VALIDATE_ONLY_UPDATED_FIELDS = 256;

    // flags for save operations about which fields to send:

    /** Partial update, send only fields which have been modified since the entity was last loaded/saved */
    const SEND_MODIFIED_FIELDS = 0;

    /** Send all fields that exist within the entity */
    const SEND_ALL_FIELDS = 1;

    /**
     * Creates a new entity. Please note that this entity won't exist on the
     * server until you save it by calling the save method.
     *
     * @return Entity
     */
    public function create();

    /**
     * Deletes an entity
     *
     * @param mixed $entity the entity to delete, or an array of multiple entities
     * @param integer $flags used only if multiple entities are provided,
     * flags described on {@link http://workspace.pipelinersales.com/community/api/data/Methods_rest.html}
     * @return Response response to the delete HTTP request
     * @throws PipelinerHttpException
     * @throws PipelinerClientException when the entity provided doesn't have an ID,
     * which usually means that it's a newly created entity that wasn't saved yet
     */
    public function delete($entity, $flags = self::FLAG_ROLLBACK_ON_ERROR);

    /**
     * Deletes one or more entities specified by their ID
     *
     * @param mixed $id the ID of the entity to delete, or an array containing multiple IDs
     * @param integer $flags used only if multiple IDs are provided,
     * flags described on {@link http://workspace.pipelinersales.com/community/api/data/Querying_rest.html}
     * @return Response response to the delete HTTP request
     * @throws PipelinerHttpException
     */
    public function deleteById($id, $flags = 0);

    /**
     * Returns a collection of all entities satisfying the provided criteria
     *
     * @param mixed $criteria Can be one of the following
     * <ul>
     * <li>null - fetches all entities up to a default limit of 25</li>
     * <li>a {@see Criteria} object</li>
     * <li>a {@see Filter} object</li>
     * <li>a {@see Sort} object</li>
     * <li>a query string following the format described at
     * {@link http://workspace.pipelinersales.com/community/api/data/Querying_rest.html}</li>
     * <li>an array with keys corresponding to the format at
     * {@link http://workspace.pipelinersales.com/community/api/data/Querying_rest.html}</li>
     * </ul>
     * @return EntityCollection
     * @throws PipelinerHttpException when retrieving data from the server fails
     * @throws PipelinerClientException when provided criteria is invalid
     */
    public function get($criteria = null);

    /**
     * Returns an entity with the specified ID
     *
     * @param string $id
     * @throws PipelinerHttpException
     * @return Entity
     */
    public function getById($id);

    /**
     * Uploads an entity to the server.
     *
     * @param mixed $entity the entity to upload, an {@see Entity} object or an associative array
     * @param integer $sendFields whether to upload all the fields for Entity objects, or just the fields
     * which have been modified in code since the entity was loaded/saved
     * @return Response|CreatedResponse response to the HTTP request
     * @throws PipelinerHttpException
     */
    public function save($entity, $sendFields = self::SEND_MODIFIED_FIELDS);

    /**
     * Updates multiple entities at once
     *
     * @param mixed $data entities to update, where each entity is either an entity object or an associative array
     * with keys corresponding to fields and values to the entity's values.
     * See the setEntities method at
     * {@link http://workspace.pipelinersales.com/community/api/data/Methods_rest.html}
     * @param integer $flags flags described on
     * {@link http://workspace.pipelinersales.com/community/api/data/Methods_rest.html}
     * @param integer $sendFields whether to upload all the fields for Entity objects, or just the fields
     * which have been modified in code since the entity was loaded/saved
     * @return Response response to the HTTP request
     * @throws PipelinerHttpException
     */
    public function bulkUpdate($data, $flags = self::FLAG_ROLLBACK_ON_ERROR, $sendFields = self::SEND_MODIFIED_FIELDS);

    /**
     * Returns an iterator set to the offset of the query's criteria
     *
     * @param EntityCollection $collection
     * @return EntityCollectionIterator
     */
    public function getEntireRangeIterator(EntityCollection $collection);
}
