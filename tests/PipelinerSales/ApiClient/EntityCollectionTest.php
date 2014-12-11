<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Tests;

use PipelinerSales\ApiClient\PipelinerClientException;
use PipelinerSales\ApiClient\EntityCollection;

class EntityCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testImmutability() {
        $a = array(1,2,4,3,6,3);
        $c = new EntityCollection($a, null, 0, 5, 8);
        try {
            $c->uksort(function($a, $b) { return ($a < $b); });
        } catch (PipelinerClientException $ex) {
            return;
        }
        $this->assertTrue(($a === $c->getArrayCopy()), 'EntityCollection should be immutable.');
    }
}
