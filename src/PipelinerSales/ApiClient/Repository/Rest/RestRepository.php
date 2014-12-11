<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Repository\Rest;

use PipelinerSales\ApiClient\Entity;
use PipelinerSales\ApiClient\EntityCollection;
use PipelinerSales\ApiClient\EntityCollectionIterator;
use PipelinerSales\ApiClient\PipelinerClientException;
use PipelinerSales\ApiClient\Repository\RepositoryInterface;
use PipelinerSales\ApiClient\Http\HttpInterface;
use PipelinerSales\ApiClient\Http\PipelinerHttpException;
use PipelinerSales\ApiClient\Http\Response;
use PipelinerSales\ApiClient\Query\Criteria;
use PipelinerSales\ApiClient\Query\Filter;
use PipelinerSales\ApiClient\Query\Sort;

/**
 * Class for retrieving and manipulating entities using Pipeliner's REST API.
 */
class RestRepository implements RepositoryInterface
{

    private $httpClient;
    private $urlPrefix;
    private $entityType;
    private $entityPlural;
    private $dateTimeFormat;

    public function __construct($urlPrefix, $entity, $entityPlural, HttpInterface $connection, $dateTimeFormat)
    {
        $this->httpClient = $connection;
        $this->urlPrefix = $urlPrefix;
        $this->entityType = $entity;
        $this->entityPlural = $entityPlural;
        $this->dateTimeFormat = $dateTimeFormat;
    }

    public function getById($id)
    {
        $result = $this->httpClient
                ->request('GET', $this->urlPrefix . '/' . $this->entityPlural . '/' . $id)
                ->decodeJson();
        return $this->decodeEntity($result);
    }

    public function get($criteria = null)
    {
        $criteriaUrl = '?';
        if (is_null($criteria)) {
            $criteriaUrl = '';
        } elseif (is_array($criteria)) {
            $criteriaUrl .= $this->arrayCriteriaToUrl($criteria);
        } elseif (is_object($criteria)) {
            $criteriaUrl .= $this->objectCriteriaToUrl($criteria);
        } elseif (is_string($criteria)) {
            $criteriaUrl .= $criteria;
        } else {
            throw new PipelinerClientException('Invalid criteria type');
        }

        $response = $this->httpClient
                ->request('GET', $this->urlPrefix . '/' . $this->entityPlural . $criteriaUrl);
        $results = $response->decodeJson();
        $decoded = array_map(array($this, 'decodeEntity'), $results);

        $range = $this->getContentRange($response);
        if ($range === null) {
            throw new PipelinerHttpException($response, 'Content-Range header not found');
        }

        return new EntityCollection($decoded, $criteria, $range['start'], $range['end'], $range['total']);
    }

    public function create()
    {
        return new Entity($this->entityType, $this->dateTimeFormat);
    }

    public function delete($entity, $flags = self::FLAG_ROLLBACK_ON_ERROR)
    {
        //if $entity is an array, it can either be a single entity
        //represented as an associative array, or an array of multiple entities,
        //i.e. [ 'ID' => 'Something' ] vs. [ [ 'ID' => 'Something1' ], [ 'ID' => 'Something2 ] ],
        //so we need to distinguish between these two cases
        if (is_array($entity)) {
            $first = reset($entity);

            //if the first element of $entity is also an array,
            //we are dealing with multiple entities
            if (is_array($first) or $first instanceof Entity) {
                return $this->deleteById(array_map(function ($item) {
                    return $item['ID'];
                }, $entity), $flags);
            }
        }

        if (!isset($entity['ID'])) {
            throw new PipelinerClientException('Cannot delete an entity which has no ID');
        }
        return $this->deleteById($entity['ID']);
    }

    public function deleteById($id, $flags = self::FLAG_ROLLBACK_ON_ERROR)
    {
        if (is_array($id)) {
            //bulk delete
            $json = json_encode($id);
            $url = $this->urlPrefix . '/deleteEntities?entityName=' . $this->entityType;
            if ($flags) {
                $url .= '&flag=' . $flags;
            }
            return $this->httpClient->request('POST', $url, $json);
        } else {
            return $this->httpClient->request('DELETE', $this->urlPrefix . '/' . $this->entityPlural . '/' . $id);
        }
    }

    public function bulkUpdate($data, $flags = self::FLAG_ROLLBACK_ON_ERROR, $sendFields = self::SEND_MODIFIED_FIELDS)
    {
        $payload = array();

        foreach ($data as $entity) {
            if ($entity instanceof Entity) {
                $values = (
                    $sendFields == self::SEND_MODIFIED_FIELDS ? $entity->getModifiedFields() : $entity->getFields()
                );
                $values['ID'] = $entity->getId();
                $payload[] = $values;
            } else {
                $payload[] = $entity;
            }
        }

        $json = json_encode($payload);
        $url = $this->urlPrefix . '/setEntities?entityName=' . $this->entityType;
        if ($flags) {
            $url .= '&flag=' . $flags;
        }
        return $this->httpClient->request('POST', $url, $json);
    }

    public function save($entity, $sendFields = self::SEND_MODIFIED_FIELDS)
    {
        if ($entity instanceof Entity) {
            $id = $entity->isFieldSet('ID') ? $entity->getId() : null;
            $data = ($sendFields == self::SEND_MODIFIED_FIELDS ? $entity->modifiedToJson() : $entity->allToJson());
        } elseif (is_array($entity)) {
            $id = isset($entity['ID']) ? $entity['ID'] : null;
            $data = json_encode($entity);
        } else {
            throw new \InvalidArgumentException('Invalid entity');
        }

        if (!empty($id)) {
            //The server currently interprets both of these:
            //  - POST to /Accounts, where the entity contains an ID
            //  - PUT to /Accounts/{id}
            //as partial updates.
            //Full PUT replacement is not implemented on the server.
            $method = 'PUT';
            $url = $this->urlPrefix . '/' . $this->entityPlural . '/' . $id;
        } else {
            $method = 'POST';
            $url = $this->urlPrefix . '/' . $this->entityPlural;
        }

        $response = $this->httpClient->request($method, $url, $data);

        //new entity was created, we need to get its ID
        if ($response->getStatusCode() == 201) {
            $response = new RestCreatedResponse($response);
            $newId = $response->getCreatedId();
            if ($entity instanceof Entity) {
                $entity->setId($newId);
            }
        }

        if (($response->getStatusCode() == 200 or $response->getStatusCode() == 201)
                and ( $entity instanceof Entity )) {
            $entity->resetModified();
        }

        return $response;
    }

    public function getEntireRangeIterator(EntityCollection $collection)
    {
        return new EntityCollectionIterator($this, $collection);
    }

    private function arrayCriteriaToUrl(array $criteria)
    {
        return http_build_query($criteria);
    }

    private function objectCriteriaToUrl($criteria)
    {
        if ($criteria instanceof Criteria) {
            return $criteria->toUrlQuery();
        } elseif ($criteria instanceof Filter) {
            $c = new Criteria();
            $c->filter($criteria);
            return $c->toUrlQuery();
        } elseif ($criteria instanceof Sort) {
            $c = new Criteria();
            $c->sort($criteria);
            return $c->toUrlQuery();
        }
        throw new PipelinerClientException('Invalid criteria object');
    }

    private function decodeEntity(\stdClass $object)
    {
        $entity = new Entity($this->entityType, $this->dateTimeFormat);

        foreach ($object as $field => $value) {
            $entity->setField($field, $value);
        }
        $entity->resetModified();

        return $entity;
    }

    private function getContentRange(Response $response)
    {
        $result = preg_match('~Content-Range: items (\d+)-([\d-]+)/(\d+)~i', $response->getHeaders(), $matches);
        if (!$result) {
            return null;
        }

        return array(
            'start' => intval($matches[1]),
            'end' => intval($matches[2]),
            'total' => intval($matches[3])
        );
    }
}
