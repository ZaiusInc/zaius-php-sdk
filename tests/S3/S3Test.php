<?php

namespace ZaiusSDK\Test\S3;

use ZaiusSDK\Test\TestAbstract;

class S3Test extends TestAbstract
{
    public function testUploadEvents()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $s3Client = $zaiusClient->getS3Client(ZAIUS_TRACKER_ID, ZAIUS_S3_KEY_ID, ZAIUS_S3_SECRET);

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
    }


    public function testUploadCustomers()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $s3Client = $zaiusClient->getS3Client(ZAIUS_TRACKER_ID, ZAIUS_S3_KEY_ID, ZAIUS_S3_SECRET);

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
    }

    public function testUploadProducts()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $s3Client = $zaiusClient->getS3Client(ZAIUS_TRACKER_ID, ZAIUS_S3_KEY_ID, ZAIUS_S3_SECRET);

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
    }

    public function testUploadOrders()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $s3Client = $zaiusClient->getS3Client(ZAIUS_TRACKER_ID, ZAIUS_S3_KEY_ID, ZAIUS_S3_SECRET);

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
    }
}
