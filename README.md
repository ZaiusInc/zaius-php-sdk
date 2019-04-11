# Zaius SDK for PHP

## Getting Started

The Zaius SDK provides a programmatic interface for the Zaius API as documented at https://developers.zaius.com/reference .

To get started, include the library in your project:

```bash
composer require zaius/zaius_sdk
```

You can get an instance of the Zaius client using:

```php
$zaiusClient = new \ZaiusSDK\ZaiusClient($apiKey);
```

The API key can be obtained from your Zaius account at https://app.zaius.com/app#/api_management . Click on the "Private" tab and use the private API key.

## Available methods

### Customer management

#### Create/update a customer

The method takes in a single array with the customer object or an array of customer objects

Example:

```php
$profile = array();
$profile['email'] = 'test3@example.com';
$ret = $zaiusClient->postCustomer($profile);
```

#### Get a customer

This method takes in a filter array with the following possible keys:
- email
- vuid
- customer_id

Note that you need to pass only one of the above. Also, the API supports only exact matches for each field.

Example:

```php
$filter = ['email'=>'clay@example.com'];
$profile = $zaiusClient->getCustomer($filter);
```

### Events

#### Post an event

Posts an event. The event array object method must have the following keys:

- type - the type of the event (i.e. product)
- action - the event's action (i.e. add_to_cart)
- identifiers - an array with the identifiers. Valid keys are vuid and email
- data - an array with the event-specific data. The full list of the supported values is available at https://developers.zaius.com/reference#upload-events

You can pass an array of events for bulk upload.

Example:

```php
$event = array();
$event['type'] = 'test';
$event['action'] = 'test';
$event['identifiers'] = ['vuid'=>'test'];
$event['data'] = ['a'=>'b'];
$ret = $zaiusClient->postEvent($event);
```

### List management

#### Create a list

Creates a list with the given name.

Example:

```php
$list = array();
$list['name'] = uniqid();

$zaiusClient->createList($list);
```

#### Get all lists

Returns all lists.

Example:

```php
$lists = $zaiusClient->getLists();
```

#### Update a list

Updates a list's name. The method expects the following parameters:
- $listId  - the ID of the list to change (get it with a getLists() call)
- $newName - the new name for the list

Example:

```php
$zaiusClient->changeListName('madison_island_newsletter','Renamed list');
```


### Subscription management

#### Get subscriptions

Gets subscriptions based on a filter. The lists of accepted filters is available at https://developers.zaius.com/reference#get-subscriptions-1 .

Example:
```php
$filters = ['email'=>'janesmith@example.com'];
$subscriptions = $zaiusClient->getSubscriptions($filters);
```

#### Update subscription

Updates a subscription. The subscription array can have three keys:
- list_id (optional) - the list for which to update the subscription
- email - the email to update the subscription for
- subscribed - true/false - whether to subscribe or unsubscribe the user

The method also supports passing an array of subscriptions for bulk updates.

Example: 

```php
$subscription = array();
$subscription['list_id'] = 'zaius_all';
$subscription['email'] = 'janesmith@example.com';
$subscription['subscribed'] = true;

$ret = $zaiusClient->updateSubscription($subscription);
```

#### Update channel opt-in

Updates a subscription. Parameters:
- optedIn - true/false
- email - email address

Example: 

```php
$zaiusClient->updateChannelOptIn(false,'janesmith@example.com');
```


### Exports

#### Export all objects

Exports all objects. Parameters:
- objects - array with objects type to export, i.e. array('orders','products'). Leave empty to export all object types
- format - one of csv, parquet. Defaults to csv
- delimiter - one of comma,tab, pipe. Defaults to comma.

Example:

```php
$zaiusClient->exportAllObjects()
```

#### Export filtered objects

Export objects given a filter.

Parameters:
- filter - an array with the filter as described at https://developers.zaius.com/v3/reference#export-filtering
- format - one of csv, parquet. Defaults to csv
- delimiter - one of comma,tab, pipe. Defaults to comma.

Example:

```php
$zaiusClient->exportObjectsWithFilters(array('object'=>'events'));
```

#### Get the export status

Gets the status of an export. Parameters:
- exportId - the export id

Example:

```php
$zaiusClient->getExportStatus(1223233);
```

### Schema API

#### Get all available objects

Returns an array with all available objects schemas.

Example:

```php
$zaiusClient->getObjects()
```

#### Get object schema

Returns the schema for the specified object.

Parameters:
- objectType - a valid object type. Get all object types with the getObjects() method

Example:

```php
 $zaiusClient->getObject('products')
```

#### Create a new object type

Creates a new object type. Parameters:
- object (unique) id
- object name
- alias
- field definition array
- relation definition array

Example:

```php
$fields =  [
    [
        'name' => 'object_id',
        "display_name"=> "New Object Identifier",
        "type"=> "string",
        "primary"=> true
    ],
    [
        "name"=> "another_field",
        "display_name"=> "Another Fields",
        "type"=> "string"
    ],
    [
        "name"=> "child_id",
        "display_name"=> "Child Identifier",
        "type"=> "number"
    ]
];

$relations = [
];

$zaiusClient->createObjectSchema('test_objects', 'Test Object', 'test_object', $fields, $relations);
        
```

#### Get an object's fields

Gets the fields for an object. Parameters:
- object id

Example:

```php
$zaiusClient->getObjectFields('products');
```

#### Create a new field for an object

Creates a new field for an object. Parameters:
- object id
- field id
- field type
- field name
- field description

Example:

```php
$zaiusClient->createObjectField('products','test_field','string','Test field','Test description');
```

#### Get all object's relations

Gets all relations for an object. Parameters:
- object id

Example:

```php
$zaiusClient->getRelations('products')
```

### Posting objects


#### Post a product

```php
$product = array();
$product['name'] = "Test product";
$product['sku'] = 'test-sku';
$product['product_id'] = 32;

$ret = $zaiusClient->postProduct($product);
```

This method also supports passing an array of products for bulk updates.

#### Post a customer

```php
$profile = array();
$profile['email'] = 'test3@example.com';
$ret = $zaiusClient->postCustomer($profile);
```

This method also supports passing an array of customers for bulk updates.

#### Post an order

```php
$order = array();
$order['name'] = "Test customer";
$order['order_id'] = '11111';
$order['total'] = 32;
$order['items'] = [[
    "product_id"=>"765",
    "sku"=>"zm64",
    "quantity"=>"1",
    "subtotal"=>"59.95"
]];

$ret = $zaiusClient->postOrder($order);
```

This method also supports passing an array of orders for bulk updates.


#### Posting custom objects

Creates (or updates) an object. Parameters:
- object id
- array with the object data. Must include all required fields per the object's schema.

This method supports passing an array of objects for bulk updates.

Example:

```php
$zaiusClient->postObject('products',['product_id'=>33,'name'=>'test product']);
```

### Bulk upload objects to S3 

The S3 client can be obtained from the Zaius Client:

```php
$zaiusClient = new \ZaiusSDK\ZaiusClient($apiKey);
$s3Client = $zaiusClient->getS3Client(ZAIUS_TRACKER_ID,ZAIUS_S3_KEY_ID,ZAIUS_S3_SECRET);
```

To get the needed keys, check https://developers.zaius.com/v3/reference#amazon-s3

#### Events

```php
$event1 = array();
$event1['type'] = 'product';
$event1['action'] = 'addtocart';
$event1['identifiers'] = ['customer_id'=>99];
$event1['data'] = ['hostname'=>'127.0.0.1','page'=>'Bar'];


$event2 = array();
$event2['type'] = 'product';
$event2['action'] = 'addtocart';
$event2['identifiers'] = ['customer_id'=>99];
$event2['data'] = ['hostname'=>'127.0.0.1','page'=>'Foo'];

$events = [$event1,$event2];

$s3Client->uploadEvents($events);
```

#### Products


```php
$product1 = array();
$product1['product_id'] = 1;
$product1['sku'] = '1234';
$product1['name'] = "Planet of the Apes";
$product1['category'] = 'Books';


$product2 = array();
$product2['product_id'] = 2;
$product2['sku'] = '4321';
$product2['name'] = "Escape from Planet of the Apes";
$product2['category']  = 'Movies';


$products = [
    $product1,$product2
];

$s3Client->uploadProducts($products);
```

#### Customers

```php
$customer1 = array();
$customer1['customer_id'] = 1100;
$customer1['email'] = "floyd22@example.com";
$customer1['first_name'] = "Floyd";
$customer1['last_name'] = 'Dogg';
$customer1['foo'] = 'bar';

$customer2 = array();
$customer2['customer_id'] = 1200;
$customer2['email'] = "johnny22@example.com";
$customer2['first_name'] = "Johnny";
$customer2['last_name'] = 'Zaius';
$customer2['foo']='bar';

$customers = [
    $customer1,$customer2
];

$s3Client->uploadCustomers($customers);
```

#### Orders

```php
$orderData = [];
$order1 = array();
$order1['order_id'] = '1009';
$order1['items']=[[
    "product_id"=>"765",
    "sku"=>"zm64",
    "quantity"=>"1",
    "subtotal"=>"59.95"
]];
$order1['subtotal'] = 6.99;
$order1['tax'] = 0;
$order1['shipping'] = 25.75;
$order1['total'] = 32.74;
$order1['email'] = 'floyd@zaius.com';
$order1['first_name'] = 'Floyd';
$order1['last_name'] = 'Dogg';
$order1['phone'] = '123456780';

$orderData['order'] = $order1;
$orderData['identifiers'] = ['ts'=>1460392935,'ip'=>'192.168.1.1','email'=>'floyd@zaius.com','action'=>'purchase'];

$orders = [$orderData];



$s3Client->uploadOrders($orders);
```
## Supported object types

All Zaius methods expect arrays in a specific format. Besides the keys listed for each object, any other key/value pair is accepted and will be sent as a custom field, if it was defined via the Zaius dashboard/schema API

To see the full list of available fields, access https://app.zaius.com/app#/custom_fields after logging in into your Zaius account.


### Customers

|Key          |Type|Other details|
|-------------|----------|-------------|
|email        |string    |             |
|gender       |string    |             |
|name         |string    |             |
|first_name   |string    |             |
|last_name    |string    |             |
|phone        |string    |             |
|timezone     |string    |       The timezone of the user in the following format: https://en.wikipedia.org/wiki/List_of_tz_database_time_zones      |
|street1      |string    |             |
|street2      |string    |             |
|city         |string    |             |
|state        |string    |             |
|zip          |string    |             |
|country      |string    |             |
|image_url    |string    |             |
|customer_id  |string    |             |

Example:

```php
$customer = array();
$customer['email'] = 'test3@example.com';
```

### Events


|Key          |Type|Other details|
|-------------|----------|-------------|
|type                    |string    |             |
|action                  |string    |             |
|identifiers.email       |string    |             |
|identifiers.vuid        |string    |             |
|data.ts                 |integer   |   Timestamp of the event in unix time (epoch time). Automatically set to now if not present.          |
|data.product_id         |string    |          |
|data.category           |string    |          |
|data.channel            |string    |          |
|data.ua                 |string    | User agent         |
|data.ip                 |string    |          |
|data.title              |string    |          |
|data.hostname           |string    |          |
|data.page               |string    |          |
|data.source             |string    |          |
|data.medium             |string    |          |
|data.campaign           |string    |          |
|data.content            |string    |          |
|data.keywords           |string    |          |
|data.language           |string    |          |
|data.character_set      |string    |          |
|data.days_since_last_visit       |integer    |          |
|data.landing            |bool    |          |
|data.referrer           |string    |          |
|data.search_term        |string    |          |
|data.filter_field       |string    |          |
|data.filter_value       |string    |          |
|data.sort_direction     |string    |          |
|data.sort_field         |string    |          |


Example:

```php
$event = array();
$event['type'] = 'test';
$event['action'] = 'test';
$event['identifiers'] = ['vuid'=>'test'];
$event['data'] = ['a'=>'b'];
```

### Products

|Key          |Type|Other details|
|-------------|----------|-------------|
|brand        |string    |         |
|category_id        |integer    |         |
|description        |string    |         |
|image_url        |string    |         |
|is_in_stock        |bool    |         |
|name        |string    |         |
|parent_product_id        |integer    |         |
|price        |number    |         |
|product_id        |string    |         |
|qty        |integer    |         |
|sku        |string    |         |
|special_price        |number    |         |
|special_price_from_date        |timestamp    |         |
|special_price_to_date        |timestamp    |         |
|upc        |string    |         |


Example:

```php
$product = array();
$product['name'] = "Test product";
$product['sku'] = 'test-sku';
$product['product_id'] = 32;
```

### Orders

|Key          |Type|Other details|
|-------------|----------|-------------|
|bill_address        |string    |         |
|coupon_code        |string    |         |
|first_name        |string    |         |
|last_name        |string    |         |
|name        |string    |         |
|discount        |number    |         |
|email        |string    |         |
|order_id        |string    |         |
|phone        |string    |         |
|ship_address        |string    |         |
|shipping        |number    |         |
|status        |text    |         |
|subtotal        |number    |         |
|tax        |number    |         |
|total        |number    |         |
|user_id        |string    |         |
|items.product_id        |string    |         |
|items.sku        |string    |         |
|items.quantity        |integer    |         |
|items.subtotal        |number    |         |

Example:

```php
$order = array();
$order['name'] = "Test customer";
$order['order_id'] = '11111';
$order['total'] = 32;
$order['items'] = [[
    "product_id"=>"765",
    "sku"=>"zm64",
    "quantity"=>"1",
    "subtotal"=>"59.95"
]];
```

## Generic API calls

While the SDK covers all specific operations, it is possible to initiate a general call to the API by using the call() method. Three parameters are expected:

- $parameters - an array of parameters. For get calls, they will be sent as url parameters. For post / put calls, they will be sent as post fields
- $method - a valid http method name, i.e. post or get
- $url - the full endpoint url

The method returns the raw API response. Note that most of the time, this will be a json encoded string.

The method throws a ZaiusException in case the response code is any other than 20x or 404. A 404 response code returns a null body and represents the fact that no entry was found for the provided query.

Example:

```php
$zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
$filter = ['email'=>'clay@example.com'];
$profile = json_decode($zaiusClient->call($filter,'get',ZaiusClient::API_URL_V3.'/profiles'),true);
```

## Batch processing

While the Zaius API is fast, it is possible to completely decouple it by using batch processing. We are making use of DJJob, https://github.com/seatgeek/djjob as a general queueing mechanism.

### Setup

Initialize the mysql database:

```bash
mysql my_database < vendor/seatgeek/djjob/jobs.sql
```

Before making a call to push to the queue, you need  to ensure that the mysql credentials were initialized:

```php
$zaiusClient->setQueueDatabaseCredentials([
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'dbname' => 'my_database',
    'user' => 'my_user',
    'password' => 'my_password',
]);
```

### Push events to the queue

All posting methods have a queue bool parameter that you can set to push to the queue instead of processing instantly:

```php
$profile = array();
$profile['email'] = 'test3@example.com';
$ret = $zaiusClient->postCustomer($profile,true);


$product = array();
$product['name'] = "Test product";
$product['sku'] = 'test-sku';
$product['product_id'] = 32;
$ret = $zaiusClient->postProduct($product,true);
```

The returned value from the post calls will be the mysql insert id of the pushed object.

You can later process the queue with:

```php
$worker = new \ZaiusSDK\Zaius\Worker();
$worker->processAll();
```