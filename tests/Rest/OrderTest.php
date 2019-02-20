<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Order;
use ZaiusSDK\Zaius\Product;
use ZaiusSDK\ZaiusException;

class OrderTest extends TestAbstract {
    public function testPostSingleOrder() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

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

        $this->assertArrayHasKey('title',$ret);
        $this->assertEquals('Accepted',$ret['title']);
    }

    public function testPostMultipleOrders() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $order1 = array();
        $order1['name'] = "Test customer 1";
        $order1['order_id'] = 'order-1';
        $order1['total'] = 50;

        $order2 = array();
        $order2['name'] = "Test customer 2";
        $order2['order_id'] = 'order-2';
        $order2['total'] = 51;

        $ret = $zaiusClient->postOrder(array($order1,$order2));

        $this->assertArrayHasKey('title',$ret);
        $this->assertEquals('Accepted',$ret['title']);
    }

}