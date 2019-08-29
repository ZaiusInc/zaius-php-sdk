<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Customer;
use ZaiusSDK\ZaiusException;

class CustomerTest extends TestAbstract
{

    /**
     * @var \ZaiusSDK\ZaiusClient
     */
    private $zaiusClient;

    public function setUp()
    {
        $this->zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
    }

    public function testPostSingleCustomer()
    {
        
        $profile = [];
        $profile['email'] = 'test3@example.com';
        $ret = $this->zaiusClient->postCustomer($profile);
        $this->assertNotFalse($ret);
        $this->assertGreaterThan(0, $ret);
    }

    public function testPostBatchCustomers()
    {
        
        $profile1 = [];
        $profile1['email']='test4@example.com';
        $profile2 = [];
        $profile2['email']='test5@example.com';
        $profiles = [$profile1,$profile2];
        $ret = $this->zaiusClient->postCustomer($profiles);
        $this->assertNotFalse($ret);
        $this->assertGreaterThan(0, $ret);
    }



    public function testGetInexistentCustomer()
    {
        $profile = ['email'=>uniqid().'-inexistent@example.com'];
        $profile = $this->zaiusClient->getCustomer($profile);
        $this->assertInternalType('array', $profile);
        $this->assertArrayHasKey('title', $profile);
        $this->assertArrayHasKey('detail', $profile);
        $this->assertArrayHasKey('message', $profile['detail']);
        $this->assertContains(
            "Unable to find profile for email",
            $profile['detail']['message']
        );
    }

    /**
     * Sadly, posting a customer is not instant, so we cannot post one and try to get it.
     * We are relying omn data that seems to exist in any demo account
     */
    public function testGetCustomerWithValidParameters()
    {
        $filter = ['email'=>'clay@example.com'];
        $profile = $this->zaiusClient->getCustomer($filter);
        $this->assertInternalType('array', $profile);

        $filter = ['customer_id'=>'99'];
        $profile = $this->zaiusClient->getCustomer($filter);
        $this->assertInternalType('array', $profile);
    }
}
