<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Repository\Rest;

use PipelinerSales\ApiClient\InfoMethodsInterface;
use PipelinerSales\ApiClient\Http\HttpInterface;

class RestInfoMethods implements InfoMethodsInterface
{

    private $baseUrl;
    private $httpClient;

    public function __construct($baseUrl, HttpInterface $httpClient)
    {
        $this->baseUrl = $baseUrl;
        $this->httpClient = $httpClient;
    }

    public function fetchTeamPipelineUrl()
    {
        return $this->httpClient->request('GET', $this->baseUrl . '/teamPipelineUrl')->decodeJson(true);
    }

    public function fetchTeamPipelineVersion()
    {
        return $this->httpClient->request('GET', $this->baseUrl . '/teamPipelineVersion')->decodeJson(true);
    }

    public function fetchServerAPIUtcDateTime()
    {
        return $this->httpClient->request('GET', $this->baseUrl . '/serverAPIUtcDateTime')->decodeJson(true);
    }

    public function fetchErrorCodes()
    {
        return $this->httpClient->request('GET', $this->baseUrl . '/errorCodes')->decodeJson(true);
    }

    public function fetchCollections()
    {
        //not / as described in the docs (no / at the end)
        return $this->httpClient->request('GET', $this->baseUrl)->decodeJson(true);
    }

    public function fetchEntityPublic()
    {
        return $this->httpClient->request('GET', $this->baseUrl . '/entityPublic')->decodeJson(true);
    }

    public function fetchEntityFields($entityName)
    {
        return $this->httpClient->request('GET', $this->baseUrl . '/getFields/' . $entityName)->decodeJson(true);
    }

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
