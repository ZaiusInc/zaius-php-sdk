<?php

namespace ZaiusSDK\Test\Rest;

use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\ZaiusException;

class OptOutTest extends TestAbstract
{
    public function testOptOutNoParams()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        try {
            $zaiusClient->optOut("test@example.com");
            $this->fail("Expected exception");
        } catch (\Exception $exception) {
            $this->assertInstanceOf(ZaiusException::class, $exception);
        }
    }

    public function testOptOut()
    {
        $zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);

        $zaiusClient->optOut("test@example.com", "clay@example.com");
    }
}
