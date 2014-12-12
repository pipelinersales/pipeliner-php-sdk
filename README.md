Pipeliner API client library for PHP
====================================
            
Introduction
------------

This library serves as a convenient wrapper around Pipeliner's [REST API](http://workspace.pipelinersales.com/community/api).
You can also view the [API documentation for the individual classes](http://pipelinersales.github.io/pipeliner-php-sdk-docs/index.html).

Installing
----------

The recommended way to install this library is via composer.
Add `"pipelinersales/pipeliner-api-client": "~1.0"` to your dependencies in `composer.json`.


An alternative way is to manually download the sources and include the `manual/pipeliner.php` file.
This will use a bundled autoloader to load all the library's classes.

The library requires PHP >= 5.3 with the cURL extension.

Basic usage
-----------

### Creating the client object

The API client object is created like this
```php
use PipelinerSales\ApiClient\PipelinerClient;

$url        = 'https://eu.pipelinersales.com';
$pipelineId = 'eu_myPipeline';
$token      = 'api token';
$password   = 'api password';

$pipeliner = PipelinerClient::create($url, $pipelineId, $token, $password);
```

The API token and password for a particular pipeline can be found in the <code>Sales Pipeline &rarr; API Access</code> section
of customer portal.

The `create` method sends a HTTP request in order to obtain the pipeline version.

### Loading data

All data can be retrieved by either directly supplying the requested item's ID
(the [`getById`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_getById) method),
or by making a query with the [`get`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_get) method.

```php
try {
    // load all accounts (up to a maximum of 25)
    $pipeliner->accounts->get();

    // load a specified account by id
    $pipeliner->accounts->getById('ID-219034053254');

}catch (PipelinerHttpException $e) {
    // something went wrong
}
```

For queries with no explicitly specified limit, up to 25 items will be returned.

The [`getById`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_getById) method returns an
[`Entity`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Entity.html) object. See the [Working with entities](#working-with-entities)
section to read about how to work with these objects. The
[`get`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_get) method returns an
[`EntityCollection`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.EntityCollection.html) object, which can be accessed
like a regular PHP array.


The library throws a [`PipelinerClientException`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.PipelinerClientException.html)
or one of its subclasses on errors. For more information, see the [Error handling](#error-handling)
section of this document.


### Types of data in the system

The types of entities that the system consists of depend on the version of the pipeline that's being worked with.
Versions 9 to 15 are supported by the current version of the library. The version of the pipeline can be
obtained by calling `$pipeliner->getPipelineVersion()`.


The list of entities along with their descriptions can be found in the API documentation at
http://workspace.pipelinersales.com/community/api/


A full list can also be obtained in code, by calling the `$pipeliner->getEntityTypes()`


In code, entities are obtained by using repositories available as properties of the client object.
Name of such a property is usually the plural of the entity name in camel case, so entities of type
`Account` can be accessed via `$pipeliner->accounts`, etc. You can also
obtain the repository with the [`getRepository`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.PipelinerClient.html#_getRepository)
method.


There are a few exceptions to this *camel case plural* rule. The full list of available repositories can be seen in the
*Magic properties summary* in the [`PipelinerClient`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.PipelinerClient.html)
class documentation.


### Querying

The criteria for which items should be retrieved consist of properties described in
[the server API docs](http://workspace.pipelinersales.com/community/api/data/Querying_rest.html)
Building the query strings manually is a hassle. The library offers several convenience classes to make it easier
and your code more readable. These are available in the [`PipelinerSales\ApiClient\Query`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/namespace-PipelinerSales.ApiClient.Query.html) namespace.


You can specify the query criteria in one of several ways. Full list is available in the
[`get`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_get) method documentation.

```php
// load up to 10 accounts and sort them by the organization name
$pipeliner->accounts->get(array('limit' => 10, 'sort' => 'ORGANIZATION'));

// load up to 5 accounts which were modified in the last week
$criteria = new Criteria();
$criteria->limit(5)->after(new DateTime('-7 days midnight'));
$pipeliner->accounts->get($criteria);

// load accounts whose organization name contains the string 'co'
$pipeliner->accounts->get(Filter::contains('ORGANIZATION', 'co'));

// load up to 10 accounts whose name starts with the letter A ordered by name in an ascending order
$pipeliner->accounts->get(
    Criteria::limit(10)->filter(Filter::startsWith('A'))->sort('NAME')
);
```


### Working with entities

Entities are represented by the [`Entity`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Entity.html) class. Their fields can be accessed
in several ways:

* Using the field name directly, with the [`getField`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Entity.html#_getField) and
  [`setField`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Entity.html#_setField) methods. Please note that all standard fields
  have names in upper case, while custom fields can contain lower case letters and other characters.
* Using the magic getters/setters. These map the method name onto field name by converting the
  camel case part into upper case and adding underscores where necessary.
* Using the array access operators, e.g. `$entity['OWNER_ID'] = 3454;`

The magic getters/setters are generally the preferred way of accessing fields. Custom fields should be set with the `getField`/`setField` methods
```php
$account = $pipeliner->accounts->getById('ID-219034053254');
echo $account->getOrganization() . "\n";
echo $account->getEmail1() . "\n";

$account->setPhone1('+100000000000')
        ->setOwnerId(1534);
```


In addition to strings and numbers, all of these methods will automatically convert `DateTime` objects
into a format expected by the server. The date/time will also be converted to UTC if it isn't already.


#### Modifying and saving data

After setting some of an entity's fields, the entity must be saved to the server with the [`save`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_save) method on the proper repository object.

```php
$account = $pipeliner->accounts->getById('ID-219034053254');
$account->setEmail1('email@example.com')
        ->setPhone1('+100000000');
$pipeliner->accounts->save($account);
```


By default, only fields which have been modified in the code will be sent to the server.
This behavior can be changed with the second argument of the
[`save`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_save) method.
After a successful save, all fields will be reset to *not modified*. 


If you have an entity object that you don't know the type of, you can obtain the proper repository by calling
`$pipeliner->getRepository($entity)`.


#### Creating new entities

New entities are created with a repository's [`create`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_create) method.
Just like with modifying data, they will not be sent to the server until the [`save`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_save)
method is called.

```php
$salesUnit = $pipeliner->salesUnits->create();
$salesUnit->setSalesUnitName('Aq');
$pipeliner->salesUnits->save($salesUnit);
```

After a successful save, the ID field of the entity object will be set to the new entity's ID.
You can also create entities by simply providing an associative array with all the fields and values.
```php
$response = $pipeliner->salesUnits->save(
    array( 'SALES_UNIT_NAME' => 'Aq' )
);
```


In case of success, the returned response will be a [`CreatedResponse`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Http.CreatedResponse.html)
object. The id of the new entity can be obtained by calling the
[`getCreatedId`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Http.CreatedResponse.html#_getCreatedId) method of this object.


#### Deleting entities
```php
$pipeliner->salesUnits->deleteById('ID-219034053254');

$account = $pipeliner->accounts->getById('ID-342534523462');
$pipeliner->accounts->delete($account);

$pipeliner->accounts->deleteById(
    array('ID-219034053254', 'ID-3456134218434', 'ID-0186703160934')
);
```


### Using collections

When loading multiple entities at once using the [`get`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Repository.RepositoryInterface.html#_get) method,
its return value will be a [`EntityCollection`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.EntityCollection.html) object. For most purposes,
it works like a regular PHP array (it extends PHP's `ArrayObject` class), but compared to arrays, it contains
some extra information about the query.

```php
$collection = $pipeliner->accounts->get();

// entity collections can be accessed like arrays
$firstAccount = $collection[0];

foreach ($collection as $account) {
    // do something with each account
}
```

One important difference is that `EntityCollection` objects are immutable (even though the elements
    inside them are themselves mutable).
```php
$accounts = $pipeliner->accounts->get();

// this is allowed
$accounts[0]->setEmail1('example@example.com');

// this throws an exception
$accounts[0] = $pipeliner->accounts->getById('ID-3452134134');
```


If you need a mutable array, you can obtain a copy of the collection's underlying array with the
`getArrayCopy` method.


### Full range iterators

When loading multiple entities, Pipeliner's REST API allows you to limit the retrieved data to only parts
of the results by specifying the `limit` and `offset` query parameters.


The [`EntityCollectionIterator`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.EntityCollectionIterator.html) class provides a convenient
way of iterating through the entire range of matching results (without the limit) while only having a part
of the data available at a time.

```php
// default limit 25 items
$accounts = $client->accounts->get();

$accountIterator = $client->accounts->getEntireRangeIterator($accounts);

$emails = array();
// this will iterate over ALL of the results (even beyond the 25)
foreach ($accountIterator as $account) {
    $emails[] = $account->getEmail1();

    if (!$accountIterator->nextDataAvailable() and !$accountIterator->atEnd()) {
        // next iteration will issue a HTTP request
    }
}
```


Initially, the iterator starts at the offset that was used in the request that returned the EntityCollection.


This class implements the [`SeekableIterator`](http://www.php.net/manual/en/class.seekableiterator.php)
interface, which allows you to arbitrarily move through the result set. Data will be fetched from the server as needed.
Currently loaded data is thrown away when new data is loaded, so it is a good idea to use this class in a way that
minimizes the number of issued HTTP requests.


### Error handling

The library uses exceptions for error handling. The base exception type it uses is
[`PipelinerClientException`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.PipelinerClientException.html), the other types
inherit it.


In case of an error response from the server, a [`PipelinerHttpException`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Http.PipelinerHttpException.html)
is thrown. If the response contained error information from the server, it can be read using the
[`getErrorMessage`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Http.PipelinerHttpException.html#_getErrorMessage) and
[`getErrorCode`](http://pipelinersales.github.io/pipeliner-php-sdk-docs/class-PipelinerSales.ApiClient.Http.PipelinerHttpException.html#_getErrorCode) methods. This information will
also be part of the standard exception message.

```php
try {
    $account = $pipeliner->accounts->create();
    
    // mandatory fields in the account are empty, so the server will refuse to save it
    $pipeliner->accounts->save($account);
}catch (PipelinerHttpException $e) {
    echo $e->getErrorMessage();
}
```

