<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Http;

/**
 * A basic interface for HTTP clients
 */
interface HttpInterface
{
    /**
     * Sends a HTTP request
     *
     * @param string $method HTTP method
     * @param string $url The URL
     * @param string $rawPayload Body of the request in case of POST/PUT requests. Must already
     * be encoded in the form expected by the server. Pipeliner's server expects JSON.
     * @param string $contentType If $rawPayload is not null, specifies the content of the Content-Type header.
     * @return Response
     * @throws PipelinerHttpException
     */
    public function request($method, $url, $rawPayload = null, $contentType = 'application/json');

    /**
     * Sets the user credentials used for authentication
     *
     * @param string $username
     * @param string $password
     */
    public function setUserCredentials($username, $password);
}
