<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\ZaiusClient;
use ZaiusSDK\ZaiusException;

class GeneralApiCallTest extends TestAbstract {


    public function testSuccessfulGet() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $filter = ['email'=>'clay@example.com'];
        $profile = json_decode($zaiusClient->call($filter,'get',ZaiusClient::API_URL_V3.'/profiles'),true);
        $this->assertInternalType('array',$profile);
    }

    public function testSuccessfulPost() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $profile = array();
        $profile['attributes']['email'] = 'test3@example.com';
        $ret = json_decode($zaiusClient->call($profile,'post',ZaiusClient::API_URL_V3.'/profiles'),true);
        $this->assertArrayHasKey('title', $ret);
        $this->assertEquals('Accepted',$ret['title']);
    }

    public function testSuccessfulPut() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $ret = $ret = json_decode($zaiusClient->call(['name'=>uniqid().'-madison-changed'],'put',ZaiusClient::API_URL_V3.'/lists/madison_island_newsletter'),true);
        $this->assertArrayHasKey('updated', $ret);
    }

    public function testErrorPost() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $filter = ['foo'=>'clay@example.com'];
        try {
            $zaiusClient->call($filter,'get',ZaiusClient::API_URL_V3.'/profiles');
            $this->fail("Expected exception");
        }
        catch(\Exception $exception) {
            $this->assertInstanceOf(ZaiusException::class,$exception);
            $this->assertStringStartsWith('Failed to',$exception->getMessage());
        }

    }

    public function testErrorPut() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        try {
            $zaiusClient->call(['foo' => uniqid() . '-madison-changed'], 'put', ZaiusClient::API_URL_V3 . '/lists/madison_island_newsletter');
            $this->fail("Expected exception");
        }
        catch(\Exception $exception) {
            $this->assertInstanceOf(ZaiusException::class,$exception);
            $this->assertStringStartsWith('Failed to',$exception->getMessage());
        }
    }

    public function testErrorGet() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $filter = ['foo'=>'clay@example.com'];
        try {
            $zaiusClient->call($filter,'get',ZaiusClient::API_URL_V3.'/profiles');
            $this->fail("Expected exception");
        }
        catch(\Exception $exception) {
            $this->assertInstanceOf(ZaiusException::class,$exception);
            $this->assertStringStartsWith('Failed to',$exception->getMessage());
        }
    }

    public function testInvalidEndpoint() {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        try {
            $zaiusClient->call([],'get',ZaiusClient::API_URL_V3.'/foo');
        }
        catch (\Exception $exception) {
            $this->assertInstanceOf(ZaiusException::class,$exception);
            $this->assertContains('403',$exception->getMessage());
        }
    }

    public function testInvalidCredentials() {
        $zaiusClient = $this->getZaiusClient('foo');
        $filter = ['email'=>'clay@example.com'];
        try {
            $zaiusClient->call($filter,'get',ZaiusClient::API_URL_V3.'/profiles');
            $this->fail("Expected exception");
        }
        catch (\Exception $ex) {
            $this->assertInstanceOf(ZaiusException::class,$ex);
            $this->assertContains('403',$ex->getMessage());
        }

    }

}