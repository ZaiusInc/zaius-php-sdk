<?php

namespace ZaiusSDK\Test;

use PHPUnit\Framework\TestCase;
use ZaiusSDK\ZaiusClient;

class TestAbstract extends TestCase {
    protected function getZaiusClient($apiKey='',$timeout=30) {
        return new ZaiusClient($apiKey,$timeout);
    }

    protected function configureQueueProcessing(ZaiusClient $zaiusClient) {
        $zaiusClient->setQueueDatabaseCredentials([
            'driver' => DB_DRIVER,
            'host' => DB_HOST,
            'dbname' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
        ]);
    }
}