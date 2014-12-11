<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Http;

use PipelinerSales\ApiClient\PipelinerClientException;

/**
 * Exception related to HTTP requests.
 */
class PipelinerHttpException extends PipelinerClientException
{

    /** @var Response $response */
    private $response;
    private $jsonError = array();

    public function __construct($response, $message, $httpError = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;

        if (!empty($httpError)) {
            $this->message .= 'HTTP error: [' . $httpError . ']';
        }

        $body = $this->response->getBody();
        if (!empty($body)) {
            $this->jsonError = json_decode($body, true);
            if (!empty($this->jsonError)) {
                $this->message .= 'Response error: [' . $this->getErrorCode() . ': ' . $this->getErrorMessage() . ']';
            }
        }

        $this->message .= ', HTTP code ' . $this->response->getStatusCode();
    }

    /**
     * @return Response
     */
    public function getHttpResponse()
    {
        return $this->response;
    }

    /**
     * The error code specified in the API, or 0 if the error response is not available.
     * @return integer
     */
    public function getErrorCode()
    {
        if (isset($this->jsonError['errorcode'])) {
            return intval($this->jsonError['errorcode']);
        }
        return 0;
    }

    /**
     * The error message specified in the API, or an empty string if not available.
     * @return string
     */
    public function getErrorMessage()
    {
        if (is_string($this->jsonError)) {
            return $this->jsonError;
        } elseif (isset($this->jsonError['message'])) {
            return $this->jsonError['message'];
        }
        return '';
    }
}
