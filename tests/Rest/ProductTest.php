<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Product;
use ZaiusSDK\ZaiusException;

/**
 * Class ProductTest
 * @package ZaiusSDK\Test\Rest
 */
class ProductTest extends TestAbstract
{
    public function testPostSingleProduct()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $product = [];
        $product['name'] = "Test product";
        $product['sku'] = 'test-sku';
        $product['product_id'] = 32;

        $ret = $zaiusClient->postProduct($product);

        $this->assertNotFalse($ret);
        $this->assertGreaterThan(0, $ret);
    }

    public function testPostMultipleProducts()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $product1 = [];
        $product1['name'] = "Test product 1";
        $product1['sku'] = 'test-sku-1';
        $product1['product_id'] = 50;

        $product2 = [];
        $product2['name'] = "Test product 2";
        $product2['sku'] = 'test-sku-2';
        $product2['product_id'] = 51;

        $ret = $zaiusClient->postProduct(array($product1,$product2));

        $this->assertNotFalse($ret);
        $this->assertGreaterThan(0, $ret);
    }
}
