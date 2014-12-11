<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Tests\Http;

use PipelinerSales\ApiClient\Http\HttpInterface;
use PipelinerSales\ApiClient\Http\Response;

class MockHttpClient implements HttpInterface
{

    public function request($method, $url, $rawPayload = null, $contentType = 'application/json')
    {
        $this->lastRequest = func_get_args();
        if ($method != 'POST') {
            return new Response('{"ID":"MOCK-1"}{"ID":"MOCK-2"}', "Content-Range: 0-1/5\r\n", 200, $url, $method);
        }else{
            return new Response('', "Location: https://something/MOCK-2\r\n", 201, $url, $method);
        }
    }

    public function setUserCredentials($username, $password)
    {

    }

    public function lastMethod()
    {
        return $this->lastRequest[0];
    }

    public function lastUrl()
    {
        return $this->lastRequest[1];
    }

    public function lastPayload()
    {
        return $this->lastRequest[2];
    }

    public $lastRequest;

}
