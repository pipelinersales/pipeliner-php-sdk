<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Tests\Query;

use PipelinerSales\ApiClient\Query\Filter;
use PipelinerSales\ApiClient\PipelinerClientException;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testStatic() {
        $str = Filter::eq('NAME', 'SomeName')->gt('COUNT', 56)->getString();
        $this->assertEquals('NAME::SomeName|COUNT::56::gt', $str);
    }

    public function testConstructor() {
        $f = new Filter('NAME::SomeName');
        $f->ne('FIRST_NAME', 'John');
        $str = $f->getString();

        $this->assertEquals('NAME::SomeName|FIRST_NAME::John::ne', $str);
    }

    public function testRawInMiddle() {
        $str = Filter::eq('NAME', 'SomeName')->raw('LAST_NAME::Doe::ll')->gt('A', 55)->getString();
        $this->assertEquals('NAME::SomeName|LAST_NAME::Doe::ll|A::55::gt', $str);
    }

    public function testLongFormMethods() {
        $str = Filter::startsWith('NAME', 'Jo')->greaterThan('DATE', '2009-12-12')->getString();
        $this->assertEquals('NAME::Jo::ll|DATE::2009-12-12::gt', $str);
    }

    public function testInvalidMethod() {
        try {
            Filter::gg('something')->getString();
        }catch(PipelinerClientException $e) {
            return;
        }
        $this->fail('Did not throw PipelinerClientException on an invalid filter operator');
    }
}
