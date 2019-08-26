<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Subscription;
use ZaiusSDK\Zaius\ZaiusList;

/**
 * Class SubscriptionsTest
 * @package ZaiusSDK\Test\Rest
 */
class SubscriptionsTest extends TestAbstract
{

    public function testGetInexistentSubscription()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $filters = ['email'=>'nonexistin222g@example.com'];

        $subscriptions = $zaiusClient->getSubscriptions($filters);
        $this->assertArrayHasKey('detail', $subscriptions);
        $this->assertArrayHasKey('message', $subscriptions['detail']);
        $this->assertEquals(
            'Customer with email nonexistin222g@example.com was not found',
            $subscriptions['detail']['message']
        );
    }

    public function testExistingSubscriptions()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $filters = ['email'=>'janesmith@example.com'];
        $subscriptions = $zaiusClient->getSubscriptions($filters);

        $this->assertInternalType('array', $subscriptions);
        $this->assertArrayHasKey('subscriptions', $subscriptions);


        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $filters = ['customer_id'=>'100'];
        $subscriptions = $zaiusClient->getSubscriptions($filters);

        $this->assertInternalType('array', $subscriptions);
        $this->assertArrayHasKey('subscriptions', $subscriptions);
    }

    public function testUpdateOptInForExistingCustomer()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $zaiusClient->updateChannelOptIn(false, 'janesmith@example.com');

        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $zaiusClient->updateChannelOptIn(true, 'janesmith@example.com');
    }

    public function testUpdateOneSubscription()
    {
        $subscription = [];
        $subscription['list_id'] = 'zaius_all';
        $subscription['email'] = 'janesmith@example.com';
        $subscription['subscribed'] = true;

        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $ret = $zaiusClient->updateSubscription($subscription);

        $this->assertTrue($ret);
    }

    public function testUpdateMoreSubscriptions()
    {
        $subscription1 = array();
        $subscription1['list_id'] = 'zaius_all';
        $subscription1['email'] = 'janesmith@example.com';
        $subscription1['subscribed'] = true;

        $subscription2 = array();
        $subscription2['list_id'] = 'zaius_all';
        $subscription2['email'] = 'jerry@example.com';
        $subscription2['subscribed'] = false;

        $subscriptions = [$subscription1,$subscription2];

        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $ret = $zaiusClient->updateSubscription($subscriptions);

        $this->assertTrue($ret);
    }

    public function testUpdateInexistentSubscriptions()
    {
        $subscription = array();
        $subscription['list_id'] = 'zaius_all';
        $subscription['email'] = uniqid().'@example.com';
        $subscription['subscribed'] = true;

        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $ret = $zaiusClient->updateSubscription($subscription);

        $this->assertTrue($ret);
    }
}
