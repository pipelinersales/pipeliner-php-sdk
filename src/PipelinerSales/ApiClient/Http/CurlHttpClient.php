<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Http;

/**
 * Implementation of HttpInterface using cURL to issue the requests.
 */
class CurlHttpClient implements HttpInterface
{

    private $extraCurlOptions = null;
    private $username;
    private $password;
    private $userAgent;

    public function __construct($userAgent = 'Pipeliner_PHP_API_Client/1.0')
    {
        $this->userAgent = $userAgent;
    }

    public function request($method, $url, $rawPayload = null, $contentType = 'application/json')
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if (!empty($rawPayload)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $rawPayload);
            curl_setopt(
                $ch,
                CURLOPT_HTTPHEADER,
                array(
                    'Content-Length: ' . strlen($rawPayload),
                    'Content-Type: ' . $contentType
                )
            );
        }

        if (!empty($this->extraCurlOptions)) {
            curl_setopt_array($ch, $this->extraCurlOptions);
        }

        if (!empty($this->username)) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        }

        $result = curl_exec($ch);

        if ($result !== false) {
            list($headers, $body) = explode("\r\n\r\n", $result, 2);
        } else {
            list($headers, $body) = array('', '');
        }
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($result === false or $status < 200 or $status > 399) {
            throw new PipelinerHttpException(
                new Response($body, $headers, $status, $url, $method),
                '',
                curl_error($ch)
            );
        }

        return new Response($body, $headers, $status, $url, $method);
    }

    public function setUserCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Sets the user agent header used in HTTP requests
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Sets extra options that will be passed to curl for every request.
     * @param array $extraOptions an array in the format expected by curl_setopt_array
     */
    public function setExtraCurlOptions(array $extraOptions)
    {
        $this->extraCurlOptions = $extraOptions;
    }
}
