<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Event;
use ZaiusSDK\ZaiusClient;
use ZaiusSDK\ZaiusException;

class EventsTest extends TestAbstract {

    public function testPostMoreThanMaxEvents() {
        $max = ZaiusClient::MAX_BATCH_SIZE;
        $max+=5;

        $events = [];
        for($i=0;$i<$max;$i++) {
            $event = array();
            $event['type'] = 'test';
            $event['action'] = 'test';
            $event['identifiers'] = ['vuid'=>'test'];
            $event['data'] = ['a'=>'b'];

            $events[]=$event;
        }

        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        try {
            $zaiusClient->postEvent($events);
            $this->fail("Expecting max batch size exception");
        }
        catch (\Exception $ex) {
            $this->assertInstanceOf(ZaiusException::class,$ex);
            $this->assertStringStartsWith('Cannot post more than',$ex->getMessage());

        }

    }

    public function testPostSingleEvent() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $event = array();
        $event['type'] = 'test';
        $event['action'] = 'test';
        $event['identifiers'] = ['vuid'=>'test'];
        $event['data'] = ['a'=>'b'];
        $ret = $zaiusClient->postEvent($event);

        $this->assertArrayHasKey('title',$ret);
        $this->assertEquals('Accepted',$ret['title']);
    }

    public function testPostBatchEvents() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $event1 = array();
        $event1['type'] = 'test1';
        $event1['action'] = 'test1';
        $event1['identifiers'] = ['vuid'=>'test'];
        $event1['data'] = ['a1'=>'b1'];

        $event2 = array();
        $event2['type'] = 'test2';
        $event2['action'] = 'test2';
        $event2['identifiers'] = ['vuid'=>'test'];
        $event2['data'] = ['a2'=>'b2'];

        $events = array($event1,$event2);
        $ret = $zaiusClient->postEvent($events);

        $this->assertArrayHasKey('title',$ret);
        $this->assertEquals('Accepted',$ret['title']);
    }

}