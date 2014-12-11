<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Tests\Model;

use PipelinerSales\ApiClient\Model\Version;

class VersionTest extends \PHPUnit_Framework_TestCase {
    public function testVersion9Count()
    {
        $this->assertEquals(23, count(Version::getEntityTypes(9)));
    }

    public function testAdditionsInVersion11()
    {
        $addition = array(
            'Competence' => 'Competencies',
            'Relevance' => 'Relevancies'
        );
        $this->assertEquals(array_merge(Version::getEntityTypes(9), $addition), Version::getEntityTypes(11));
    }

    public function testRemovalOfCompetenceIn15()
    {
        $this->assertArrayNotHasKey('Competence', Version::getEntityTypes(15));
        $this->assertEquals(33, count(Version::getEntityTypes(15)));
    }
}