<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

namespace PipelinerSales\ApiClient\Tests\Query;

use PipelinerSales\ApiClient\Query\Criteria;

class CriteriaTest extends \PHPUnit_Framework_TestCase
{

    public function testStaticCall()
    {
        $filterString = 'NAME::John::ge';
        $this->assertEquals('filter=' . urlencode($filterString), Criteria::filter($filterString)->toUrlQuery());
    }

    public function testChaining()
    {
        $filterString = 'NAME::John::ge';
        $limit = 10;

        $criteria = new Criteria();
        $criteria->filter($filterString);
        $criteria->limit($limit);

        $this->assertEquals($criteria, Criteria::filter($filterString)->limit($limit));
    }

    public function testSetFilter()
    {
        $criteria = new Criteria();
        $criteria->filter('NAME::John::ge');
        $query = $criteria->toUrlQuery();

        $this->assertEquals('filter=' . urlencode('NAME::John::ge'), $query);
    }

    public function testFromString()
    {
        $params = array(
            'filter' => 'NAME::John::ge',
            'limit' => 5,
            'offset' => 10
        );
        $criteria = new Criteria(http_build_query($params));

        $this->assertEquals($criteria->getLimit(), 5);
        $this->assertEquals($criteria->getOffset(), 10);
        $this->assertEquals($criteria->getFilter(), 'NAME::John::ge');
    }

    public function testCopyConstructor()
    {
        $criteria = new Criteria(array(
            'limit' => 5,
            'filter' => 'NAME::John::ge'
        ));

        $criteria2 = new Criteria($criteria);
        $this->assertEquals($criteria, $criteria2);
    }

}
