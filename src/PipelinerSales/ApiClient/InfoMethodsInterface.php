<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient;

/**
 * Methods for retrieving information describing data and configuration on the server.
 *
 * See the <i>Miscellaneous REST methods</i> section at
 * {@link http://workspace.pipelinersales.com/community/api/data/Methods_rest.html} for details.
 */
interface InfoMethodsInterface
{

    /**
     * @return string
     */
    public function fetchTeamPipelineUrl();

    /**
     * @return integer
     */
    public function fetchTeamPipelineVersion();

    /**
     * @return string
     */
    public function fetchServerAPIUtcDateTime();

    /**
     * Returns a list of possible error codes along with their messages
     *
     * @return array
     */
    public function fetchErrorCodes();

    /**
     * Returns a list of existing types of collections
     *
     * @return string[]
     */
    public function fetchCollections();

    /**
     * Returns a list of existing types of entities
     *
     * @return string[]
     */
    public function fetchEntityPublic();

    /**
     * Returns a list of fields that exist for a specified entity
     *
     * @param Entity|string $entity an entity object or a string with the entity's type (e.g. Account)
     * @return array
     */
    public function fetchEntityFields($entity);
}
