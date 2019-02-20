<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\ZaiusList;
use ZaiusSDK\ZaiusException;

class ListsTest extends TestAbstract {

    public function testCreateSingleList() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $list = array();
        $list['name'] = uniqid();

        $zaiusClient->createList($list);
    }

    public function testGetLists() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $lists = $zaiusClient->getLists();

        $this->assertInternalType('array',$lists);
        $this->assertGreaterThan(1,$lists);
    }

    public function testChangeList() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $zaiusClient->changeListName('madison_island_newsletter',uniqid().'-madison-changed');
    }

    public function testChangeInexistentList() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        try {
            $zaiusClient->changeListName(uniqid(),uniqid().'-changed');
            $this->fail("Expected exception");
        }
        catch(\Exception $ex) {
            $this->assertInstanceOf(ZaiusException::class,$ex);
        }

    }


}