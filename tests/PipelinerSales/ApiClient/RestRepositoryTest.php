<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Tests;

use PipelinerSales\ApiClient\Entity;
use PipelinerSales\ApiClient\PipelinerClientException;
use PipelinerSales\ApiClient\Repository\RepositoryInterface;
use PipelinerSales\ApiClient\Repository\Rest\RestRepository;
use PipelinerSales\ApiClient\Tests\Http\MockHttpClient;

require_once 'tests/PipelinerSales/ApiClient/Http/MockHttpClient.php';

class RestRepositoryTest extends \PHPUnit_Framework_TestCase
{

    public function testCreate()
    {
        $mockConnection = $this->getMock('\\PipelinerSales\\ApiClient\\Http\\HttpInterface');
        $accountRepo = new RestRepository('/', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        $account = $accountRepo->create();
        $this->assertInstanceOf('\\PipelinerSales\\ApiClient\\Entity', $account);
    }

    public function testSaveArrayPartial()
    {
        $mockConnection = new MockHttpClient();
        $accountRepo = new RestRepository('', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        $accountRepo->save(array(
            "ID" => "8"
        ));

        $this->assertEquals('PUT', $mockConnection->lastMethod());
        $this->assertEquals('/Accounts/8', $mockConnection->lastUrl());
        $this->assertEquals('{"ID":"8"}', $mockConnection->lastPayload());
    }

    public function testSaveFull()
    {
        $mockConnection = new MockHttpClient();
        $accountRepo = new RestRepository('', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        $accountRepo->save(
            array("ID" => "8"),
            RepositoryInterface::SEND_ALL_FIELDS
        );

        $this->assertEquals('PUT', $mockConnection->lastMethod());
        $this->assertEquals('/Accounts/8', $mockConnection->lastUrl());
        $this->assertEquals('{"ID":"8"}', $mockConnection->lastPayload());
    }

    public function testSaveNew()
    {
        $mockConnection = new MockHttpClient();
        $accountRepo = new RestRepository('', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        $result = $accountRepo->save(array(
            "ORGANIZATION" => "Asdf"
        ));

        $this->assertEquals('POST', $mockConnection->lastMethod());
        $this->assertEquals('/Accounts', $mockConnection->lastUrl());
        $this->assertEquals('MOCK-2', $result->getCreatedId());
    }

    public function testDeleteEntity()
    {
        $mockConnection = new MockHttpClient();
        $accountRepo = new RestRepository('', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        $accountRepo->delete(array(
            'ID' => 9
        ));

        $this->assertEquals('DELETE', $mockConnection->lastMethod());
        $this->assertEquals('/Accounts/9', $mockConnection->lastUrl());
    }


    public function testDeleteWithoutId()
    {
        $mockConnection = new MockHttpClient();
        $accountRepo = new RestRepository('', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        try {
            $accountRepo->delete(array(
                'ORGANIZATION' => 'Asdf'
            ));
        } catch (PipelinerClientException $e) {
            return;
        }
        $this->fail('Delete without id should throw an exception.');
    }

    public function testDeleteBulk()
    {
        $mockConnection = new MockHttpClient();
        $accountRepo = new RestRepository('', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        $accountRepo->delete(array(
            array('ID' => 9),
            array('ID' => 10),
            array('ID' => 11)
        ), 1);

        $this->assertEquals('POST', $mockConnection->lastMethod());
        $this->assertEquals('/deleteEntities?entityName=Account&flag=1', $mockConnection->lastUrl());
        $this->assertEquals('[9,10,11]', $mockConnection->lastPayload());
    }

    public function testBulkUpdate()
    {
        $mockConnection = new MockHttpClient();
        $accountRepo = new RestRepository('', 'Account', 'Accounts', $mockConnection, 'Y-m-d H:i:s');

        $account1 = new Entity('Account', 'Y-m-d H:i:s');
        $account2 = clone $account1;
        $account3 = clone $account1;

        $account1->setId(6)->setOrganization('Asdf');
        $account2->setId(8);
        $account3->setId(12);

        $accountRepo->bulkUpdate(array($account1, $account2, $account3));

        $this->assertEquals('POST', $mockConnection->lastMethod());
        $this->assertEquals('/setEntities?entityName=Account', $mockConnection->lastUrl());
        $this->assertEquals('[{"ID":6,"ORGANIZATION":"Asdf"},{"ID":8},{"ID":12}]', $mockConnection->lastPayload());
    }

}
