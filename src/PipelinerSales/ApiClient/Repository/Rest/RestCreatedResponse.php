<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Repository\Rest;

use PipelinerSales\ApiClient\Http\Response;
use PipelinerSales\ApiClient\Http\CreatedResponse;

class RestCreatedResponse extends CreatedResponse
{
    public function __construct(Response $r)
    {
        parent::__construct(
            $r->getBody(),
            $r->getHeaders(),
            $r->getStatusCode(),
            $r->getRequestUrl(),
            $r->getRequestMethod()
        );
    }
    
    public function getCreatedId()
    {
        //Response to a POST request creating a new entity has a Location header
        //which contains the URL of that entity. Its ID is after the last slash
        //in that URL.
        $headers = $this->getHeaders();
        $locationPos = strpos($headers, "\r\nLocation:");
        $lineEndPos = strpos($headers, "\r\n", $locationPos + 1);
        $locationLine = substr($headers, $locationPos, $lineEndPos - $locationPos);
        $finalSlashPos = strrpos($locationLine, '/');
        return substr($locationLine, $finalSlashPos + 1);
    }
}
