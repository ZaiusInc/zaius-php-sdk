<?php

namespace ZaiusSDK\Test\S3;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Worker;
use ZaiusSDK\ZaiusException;

class BatchTest extends TestAbstract {
    public function testCredentialsNotSet() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $event = array();
        $event['type'] = 'test';
        $event['action'] = 'test';
        $event['identifiers'] = ['vuid'=>'test'];
        $event['data'] = ['a'=>'b'];

        try {
            $zaiusClient->postEvent($event,true);
            $this->fail("Expected exception");
        }
        catch(\Exception $ex) {
            $this->assertStringStartsWith("DJJob couldn't connect to the database",$ex->getMessage());
        }
    }

    public function testBatchEvent() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $this->configureQueueProcessing($zaiusClient);

        $event = array();
        $event['type'] = 'test';
        $event['action'] = 'test';
        $event['identifiers'] = ['vuid'=>'test'];
        $event['data'] = ['a'=>'b'];

        $ret = $zaiusClient->postEvent($event,true);
        $this->assertGreaterThan(0,$ret);

        $worker = new Worker();
        $worker->processAll();
    }

    public function testBatchVarious() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $this->configureQueueProcessing($zaiusClient);

        $profile = array();
        $profile['email'] = 'test3@example.com';
        $ret = $zaiusClient->postCustomer($profile,true);
        $this->assertGreaterThan(0,$ret);

        $list = array();
        $list['name'] = uniqid();
        $ret = $zaiusClient->createList($list);
        $this->assertGreaterThan(0,$ret);

        $ret = $zaiusClient->changeListName('madison_island_newsletter',uniqid().'-madison-changed');
        $this->assertGreaterThan(0,$ret);

        $ret = $zaiusClient->postObject('products',['product_id'=>33,'name'=>'test product'],true);
        $this->assertGreaterThan(0,$ret);

        $ret = $zaiusClient->optOut("test@example.com","clay@example.com");
        $this->assertGreaterThan(0,$ret);


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
        $ret = $zaiusClient->postOrder($order,true);
        $this->assertGreaterThan(0,$ret);

        $product = array();
        $product['name'] = "Test product";
        $product['sku'] = 'test-sku';
        $product['product_id'] = 32;
        $ret = $zaiusClient->postProduct($product,true);
        $this->assertGreaterThan(0,$ret);

        $ret = $zaiusClient->updateChannelOptIn(false,'janesmith@example.com');
        $this->assertGreaterThan(0,$ret);

        $subscription1 = array();
        $subscription1['list_id'] = 'zaius_all';
        $subscription1['email'] = 'janesmith22@example.com';
        $subscription1['subscribed'] = true;
        $subscription2 = array();
        $subscription2['list_id'] = 'zaius_all';
        $subscription2['email'] = 'jerry22@example.com';
        $subscription2['subscribed'] = false;
        $subscriptions = [$subscription1,$subscription2];
        $ret = $zaiusClient->updateSubscription($subscriptions);
        $this->assertGreaterThan(0,$ret);

        $worker = new Worker();
        $worker->processAll();
    }
}