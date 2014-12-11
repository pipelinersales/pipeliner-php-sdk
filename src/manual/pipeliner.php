<?php
/**
 * This file is part of the Pipeliner API client library for PHP
 *
 * Copyright 2014 Pipelinersales, Inc. All Rights Reserved.
 * For the full license information, see the attached LICENSE file.
 */

require __DIR__ . '/SplClassLoader.php';

$classLoader = new PipelinerSales\ApiClient\SplClassLoader('PipelinerSales', __DIR__ . '/..');
$classLoader->register();
