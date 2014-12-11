<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Http;

/**
 * Represents a HTTP response.
 */
class Response
{

    private $body;
    private $headers;
    private $statusCode;
    private $requestMethod;
    private $requestUrl;

    public function __construct($body, $headers, $statusCode, $requestUrl, $requestMethod)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
        $this->requestUrl = $requestUrl;
        $this->requestMethod = $requestMethod;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns the raw headers of the response in a single string
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getRequestMethod()
    {
        return $this->requestMethod;
    }

    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * Decodes the response's body into an object.
     * @param boolean $assoc when true, returned objects will be converted into associative arrays.
     * @return \stdClass|array
     * @throws PipelinerHttpException on error while decoding the json string
     */
    public function decodeJson($assoc = false)
    {
        $result = json_decode($this->body, $assoc);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PipelinerHttpException($this, "Error while parsing returned JSON");
        }

        return $result;
    }
}
