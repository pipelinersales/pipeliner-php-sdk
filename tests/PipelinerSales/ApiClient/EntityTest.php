<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Tests;

use PipelinerSales\ApiClient\Entity;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    public function testDateTimeInput() {
        $entity = new Entity('Account', 'Y-m-d H:i:s');
        $dt = new \DateTime('now', new \DateTimeZone('UTC'));
        $dt->setDate(2014, 5, 6);
        $dt->setTime(14, 0, 2);
        $entity->setModified($dt);
        $this->assertEquals('2014-05-06 14:00:02', $entity->getModified());
    }

    public function testDateTimeUTC() {
        $entity = new Entity('Account', 'Y-m-d H:i:s');
        $dt = new \DateTime('2014-10-10 10:10:10', new \DateTimeZone('Europe/Berlin'));
        $entity->setField('MODIFIED', $dt);
        $this->assertEquals('2014-10-10 08:10:10', $entity->getField('MODIFIED'));
    }

    public function testArrayAccess() {
        $entity = new Entity('Account', 'Y-m-d H:i:s');
        $entity->setId(1234);
        $this->assertEquals(1234, $entity['ID']);
    }
}
