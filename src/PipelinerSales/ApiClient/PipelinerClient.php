<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient;

use PipelinerSales\ApiClient\Http\CurlHttpClient;
use PipelinerSales\ApiClient\Http\PipelinerHttpException;
use PipelinerSales\ApiClient\Model\Version;
use PipelinerSales\ApiClient\Repository\RepositoryInterface;
use PipelinerSales\ApiClient\Repository\RepositoryFactoryInterface;
use PipelinerSales\ApiClient\Repository\Rest\RestRepositoryFactory;
use PipelinerSales\ApiClient\Repository\Rest\RestInfoMethods;

/**
 * The client context
 *
 * @property-read RepositoryInterface $accounts
 * @property-read RepositoryInterface $accountTypes
 * @property-read RepositoryInterface $activities
 * @property-read RepositoryInterface $activityTypes
 * @property-read RepositoryInterface $addressbookRelations (pipeline version 14+)
 * @property-read RepositoryInterface $appointments
 * @property-read RepositoryInterface $clients
 * @property-read RepositoryInterface $competencies (pipeline version 11-14)
 * @property-read RepositoryInterface $contacts
 * @property-read RepositoryInterface $currencies
 * @property-read RepositoryInterface $data
 * @property-read RepositoryInterface $documents
 * @property-read RepositoryInterface $emails (pipeline version 12+)
 * @property-read RepositoryInterface $exRateLists
 * @property-read RepositoryInterface $industries
 * @property-read RepositoryInterface $integrationEnvs
 * @property-read RepositoryInterface $leads
 * @property-read RepositoryInterface $masterRights
 * @property-read RepositoryInterface $messages
 * @property-read RepositoryInterface $notes
 * @property-read RepositoryInterface $opportunities
 * @property-read RepositoryInterface $opptyAccountRelations (pipeline version 14+)
 * @property-read RepositoryInterface $opptyContactRelations (pipeline version 14+)
 * @property-read RepositoryInterface $opptyContactRoles (pipeline version 15+)
 * @property-read RepositoryInterface $opptyProductRelations (pipeline version 14+)
 * @property-read RepositoryInterface $products
 * @property-read RepositoryInterface $productCategories (pipeline version 14+)
 * @property-read RepositoryInterface $productPriceLists (pipeline version 14+)
 * @property-read RepositoryInterface $productPriceListPrice (pipeline version 14+)
 * @property-read RepositoryInterface $reasonOfCloses
 * @property-read RepositoryInterface $relevancies (pipeline version 11-14)
 * @property-read RepositoryInterface $reminders
 * @property-read RepositoryInterface $salesRoles (pipeline version 15+)
 * @property-read RepositoryInterface $salesUnits
 * @property-read RepositoryInterface $stages
 */
class PipelinerClient
{

    private $entitiesToCollections;
    private $collectionsToEntities;
    private $repositories = array();
    private $repositoryFactory;
    private $infoMethods;
    private $pipelineVersion;

    /**
     * Constructor. To create a client object configured for most typical uses, use the static {@see create} method.
     *
     * @param array $entityTypes an associative array with entity names as keys and their plurals as values
     * @param RepositoryFactoryInterface $repositoryFactory
     * @param InfoMethodsInterface $infoMethods
     */
    public function __construct(
        array $entityTypes,
        RepositoryFactoryInterface $repositoryFactory,
        InfoMethodsInterface $infoMethods
    ) {
        $this->setEntityTypes($entityTypes);
        $this->repositoryFactory = $repositoryFactory;
        $this->infoMethods = $infoMethods;
    }

    /**
     * Creates a PipelinerClient object with sensible default configuration.
     * Will perform a HTTP request to fetch the pipeline version.
     *
     * @param string $url base url of the REST server, without the trailing slash
     * @param string $pipelineId the unique team pipeline id
     * @param string $apiToken API token
     * @param string $password API password
     * @return PipelinerClient
     * @throws PipelinerClientException when trying to use an unsupported pipeline version
     * @throws PipelinerHttpException if fetching the pipeline version fails
     */
    public static function create($url, $pipelineId, $apiToken, $password)
    {
        $baseUrl = $url . '/rest_services/v1/' . $pipelineId;

        $httpClient = new CurlHttpClient();
        $httpClient->setUserCredentials($apiToken, $password);

        $dateTimeFormat = Defaults::DATE_FORMAT;
        $repoFactory = new RestRepositoryFactory($baseUrl, $httpClient, $dateTimeFormat);

        $infoMethods = new RestInfoMethods($baseUrl, $httpClient);
        $version = $infoMethods->fetchTeamPipelineVersion();

        if ($version < Version::EARLIEST_VERSION) {
            throw new PipelinerClientException(
                'Unsupported team pipeline version: ' . $version .
                ' (supported versions are ' . Version::EARLIEST_VERSION . ' to ' . Version::LATEST_VERSION . ')'
            );
        }

        $entityTypes = Version::getEntityTypes($version);

        $client = new PipelinerClient($entityTypes, $repoFactory, $infoMethods);
        $client->pipelineVersion = $version;
        return $client;
    }

    /**
     * Returns an associative array of recognized entity names to the plurals of their names
     * @return array
     */
    public function getEntityTypes()
    {
        return $this->entitiesToCollections;
    }

    /**
     * Returns an object for retrieving various information from the server.
     * @return InfoMethodsInterface
     */
    public function getServerInfo()
    {
        return $this->infoMethods;
    }

    /**
     * Magic getter for repositories. Calls {@see getRepository} for
     * known entity types.
     *
     * @param string $name camelCase name of the collection (e.g. activityTypes)
     * @return RepositoryInterface
     */
    public function __get($name)
    {
        $entityName = $this->collectionsToEntities[ucfirst($name)];
        return $this->getRepository($entityName);
    }

    /**
     * Returns a repository for the specified entity.
     *
     * @param mixed $entityName an {@see Entity} object or entity name,
     * can be both singular (Account) and plural (Accounts)
     * @return RepositoryInterface
     */
    public function getRepository($entityName)
    {
        if ($entityName instanceof Entity) {
            $entityName = $entityName->getType();
        }

        if (!isset($this->repositories[$entityName])) {
            $plural = $this->getCollectionName($entityName);
            $this->repositories[$entityName] = $this->repositoryFactory->createRepository(
                $entityName,
                $plural
            );
        }

        return $this->repositories[$entityName];
    }

    private function setEntityTypes($entitiesToCollections)
    {
        $this->entitiesToCollections = $entitiesToCollections;
        $this->collectionsToEntities = array_flip($this->entitiesToCollections);
    }

    private function getCollectionName($entityName)
    {
        return $this->entitiesToCollections[$entityName];
    }

    public function getPipelineVersion()
    {
        return $this->pipelineVersion;
    }

    public function registeryEntityType($entityName, $collectionName)
    {
        $this->entitiesToCollections[$entityName] = $collectionName;
    }
}
