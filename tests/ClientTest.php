<?php

namespace ZaiusSDK\Test;

use ZaiusSDK\Zaius\Worker;
use ZaiusSDK\ZaiusException;

/**
 * Class ClientTest
 * @package ZaiusSDK\Test\S3
 */
class ClientTest extends TestAbstract
{
    /**
     * @var \ZaiusSDK\ZaiusClient
     */
    private $zaiusClient;

    public function setUp()
    {
        $this->zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
    }

    public function testCredentialsNotSet()
    {
        $zaiusClient = $this->getZaiusClient();
        
        $event = [
            'type' => 'test',
            'action' => 'test',
            'identifiers' => ['vuid'=>'test'],
            'data' => ['a'=>'b']
        ];

        try {
            $zaiusClient->call($event, 'POST', self::API_URL_V3.'/events');
            $this->fail("Expected exception");
        }
        catch(ZaiusException $ex) {
            $this->assertStringEndsWith(
                '{"message":"Forbidden"}',
                $ex->getMessage()
            );
        }
    }

    public function testManyEvents()
    {
        
        $this->configureQueueProcessing($this->zaiusClient);

        $profile = [];
        $profile['email'] = 'test3@example.com';
        $ret = $this->zaiusClient->postCustomer($profile, true);
        $this->assertGreaterThan(0, $ret);

        $list = [];
        $list['name'] = uniqid();
        $ret = $this->zaiusClient->createList($list);
        $this->assertGreaterThan(0, $ret);

        $ret = $this->zaiusClient->changeListName(
            'madison_island_newsletter',
            uniqid().'-madison-changed'
        );
        $this->assertGreaterThan(0, $ret);

        $ret = $this->zaiusClient->postObject(
            'products',
            ['product_id'=>33,'name'=>'test product'],
            true
        );
        $this->assertGreaterThan(0, $ret);

        $ret = $this->zaiusClient->optOut(
            "test@example.com",
            "clay@example.com"
        );
        $this->assertGreaterThan(0, $ret);

        $order = [
            'name' => "Test customer",
            'order_id' => '11111',
            'total' => 32,
            'items' => [[
                "product_id"=>"765",
                "sku"=>"zm64",
                "quantity"=>"1",
                "subtotal"=>"59.95"
            ]]
        ];
        $ret = $this->zaiusClient->postOrder($order, true);
        $this->assertGreaterThan(0, $ret);

        $product = [
            'name' => "Test product",
            'sku' => 'test-sku',
            'product_id' => 32
        ];
        $ret = $this->zaiusClient->postProduct($product, true);
        $this->assertGreaterThan(0, $ret);

        $ret = $this->zaiusClient->updateChannelOptIn(
            false,
            'janesmith@example.com'
        );
        $this->assertGreaterThan(0, $ret);

        $subscription1 = [
            'list_id' => 'zaius_all',
            'email' => 'janesmith22@example.com',
            'subscribed' => true
        ];
        $subscription2 = [
            'list_id' => 'zaius_all',
            'email' => 'jerry22@example.com',
            'subscribed' => false
        ];
        $subscriptions = [$subscription1,$subscription2];
        $ret = $this->zaiusClient->updateSubscription($subscriptions);
        $this->assertGreaterThan(0, $ret);

        $worker = new Worker();
        $worker->processAll();
    }
}