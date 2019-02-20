<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Customer;
use ZaiusSDK\ZaiusException;

class CustomerTest extends TestAbstract {
    public function testPostSingleCustomer() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $profile = array();
        $profile['email'] = 'test3@example.com';
        $ret = $zaiusClient->postCustomer($profile);
        $this->assertArrayHasKey('title', $ret);
        $this->assertEquals('Accepted',$ret['title']);
    }

    public function testPostBatchCustomers() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $profile1 = array();
        $profile1['email']='test4@example.com';
        $profile2 = array();
        $profile2['email']='test5@example.com';
        $profiles = array($profile1,$profile2);
        $ret = $zaiusClient->postCustomer($profiles);
        $this->assertArrayHasKey('title', $ret);
        $this->assertEquals('Accepted',$ret['title']);
    }



    public function testGetInexistentCustomer() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $profile = ['email'=>uniqid().'-inexistent@example.com'];
        $profile = $zaiusClient->getCustomer($profile);

        $this->assertNull($profile);
    }

    /**
     * Sadly, posting a customer is not instant, so we cannot post one and try to get it.
     * We are relying omn data that seems to exist in any demo account
     */
    public function testGetCustomerWithValidParameters() {

        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $filter = ['email'=>'clay@example.com'];
        $profile = $zaiusClient->getCustomer($filter);
        $this->assertInternalType('array',$profile);

        $filter = ['customer_id'=>'99'];
        $profile = $zaiusClient->getCustomer($filter);
        $this->assertInternalType('array',$profile);

    }
}