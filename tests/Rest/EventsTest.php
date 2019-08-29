<?php

namespace ZaiusSDK\Test\Rest;

use PHPUnit\Runner\Exception;
use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Event;
use ZaiusSDK\ZaiusClient;
use ZaiusSDK\ZaiusException;

class EventsTest extends TestAbstract
{
    /**
     * @var \ZaiusSDK\ZaiusClient
     */
    private $zaiusClient;

    public function setUp()
    {
        $this->zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
    }

    public function testPostMoreThanMaxEvents()
    {
        $max = ZaiusClient::MAX_BATCH_SIZE;
        $max+=1000;

        $events = [];
        for ($i=0;$i<$max;$i++) {
            $events[] = [
                'type' => 'test',
                'action' => 'test',
                'identifiers' => ['vuid'=>'test'],
                'data' => ['a'=>'b']
            ];
        }
        
        try {
            $this->zaiusClient->postEvent($events);
            $this->fail("Expecting max batch size exception");
        } catch (ZaiusException $ex) {
            $this->assertInstanceOf(ZaiusException::class, $ex);
            $this->assertStringStartsWith(
                'Cannot post more than',
                $ex->getMessage()
            );
        }
    }

    public function testPostSingleEvent()
    {
        $event = [];
        $event['type'] = 'test';
        $event['action'] = 'test';
        $event['identifiers'] = ['vuid'=>'test'];
        $event['data'] = ['a'=>'b'];
        $ret = $this->zaiusClient->postEvent($event);

        $this->assertNotFalse($ret);
        $this->assertGreaterThan(0, $ret);
    }

    public function testPostBatchEvents()
    {
        $event1 = [];
        $event1['type'] = 'product';
        $event1['action'] = 'add_to_cart';
        $event1['identifiers'] = ["email" => "tyler@zaius.com"];
        $event1['data'] = ["product_id" => "123"];

        $event2 = [];
        $event2['type'] = 'product';
        $event2['action'] = 'remove_from_cart';
        $event2['identifiers'] = ["email" => "tyler@zaius.com"];
        $event2['data'] = ["product_id" => "123"];

        $events = [$event1,$event2];
        $ret = $this->zaiusClient->postEvent($events);

        $this->assertNotFalse($ret);
        $this->assertGreaterThan(0, $ret);
    }
}
