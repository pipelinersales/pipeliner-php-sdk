<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Model;

/**
 * Class Version
 */
class Version
{
    const EARLIEST_VERSION = 9;
    const LATEST_VERSION = 15;

    private static $entitiesToCollections = array(
        9 => array(
            'add' => array(
                'Account' => 'Accounts',
                'AccountType' => 'AccountTypes',
                'Activity' => 'Activities',
                'ActivityType' => 'ActivityTypes',
                'Appointment' => 'Appointments',
                'Client' => 'Clients',
                'Contact' => 'Contacts',
                'Currency' => 'Currencies',
                'Data' => 'Data',
                'Document' => 'Documents',
                'ExchangeRateList' => 'ExRateLists',
                'Industry' => 'Industries',
                'IntegrationEnvironment' => 'IntegrationEnvs',
                'Lead' => 'Leads',
                'MasterRight' => 'MasterRights',
                'Message' => 'Messages',
                'Note' => 'Notes',
                'Opportunity' => 'Opportunities',
                'Product' => 'Products',
                'ReasonOfClose' => 'ReasonOfCloses',
                'Reminder' => 'Reminders',
                'SalesUnit' => 'SalesUnits',
                'Stage' => 'Stages'
            )
        ),
        11 => array(
            'add' => array(
                'Competence' => 'Competencies',
                'Relevance' => 'Relevancies'
            )
        ),
        12 => array(
            'add' => array(
                'Email' => 'Emails'
            )
        ),
        14 => array(
            'add' => array(
                'AddressbookRelation' => 'AddressbookRelations',
                'OpptyAccountRelation' => 'OpptyAccountRelations',
                'OpptyContactRelation' => 'OpptyContactRelations',
                'OpptyProductRelation' => 'OpptyProductRelations',
                'ProductCategory' => 'ProductCategories',
                'ProductPriceList' => 'ProductPriceLists',
                'ProductPriceListPrice' => 'ProductPriceListPrices'
            )
        ),
        15 => array(
            'add' => array(
                'OpptyContactRole' => 'OpptyContactRoles',
                'SalesRole' => 'SalesRoles'
            ),
            'remove' => array(
                'Competence' => true,
                'Relevance' => true
            )
        )
    );

    /**
     * @param $version
     * @return array
     */
    public static function getEntityTypes($version)
    {
        $entityTypes = array();
        for ($i = self::EARLIEST_VERSION; $i <= min(self::LATEST_VERSION, $version); $i++) {
            if (!isset(self::$entitiesToCollections[$i])) {
                continue;
            }

            if (isset(self::$entitiesToCollections[$i]['add'])) {
                $entityTypes = array_merge($entityTypes, self::$entitiesToCollections[$i]['add']);
            }

            if (isset(self::$entitiesToCollections[$i]['remove'])) {
                $entityTypes = array_diff_key($entityTypes, self::$entitiesToCollections[$i]['remove']);
            }
        }
        return $entityTypes;
    }
}
