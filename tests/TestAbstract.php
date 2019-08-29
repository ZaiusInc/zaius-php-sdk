<?php

namespace ZaiusSDK\Test;

use PHPUnit\Framework\TestCase;
use ZaiusSDK\Batch\ZaiusBatchRequest;
use ZaiusSDK\HttpClients\CurlHttpClient;
use ZaiusSDK\HttpClients\GuzzleHttpClient;
use ZaiusSDK\ZaiusClient;
use ZaiusSDK\ZaiusRequest;

class TestAbstract extends TestCase
{

    const API_URL_V3 = ZaiusClient::API_URL_V3;

    protected function getZaiusClient($apiKey='',$timeout=30) 
    {
        return new ZaiusClient($apiKey, $timeout);
    }

    protected function getZaiusHttpClientCurl(){
        return new CurlHttpClient();
    }

    protected function getGuzzleHttpClient(){
        return new GuzzleHttpClient();
    }

    protected function getZaiusBatchRequest(
        ZaiusClient $zaiusClient,
        array $requests = []
    ) {
        return new ZaiusBatchRequest($zaiusClient, $requests);
    }

    protected function getZaiusRequest(
        ZaiusClient $zaiusClient,
        $method = null,
        $endpoint = null,
        array $params = []
    ) {
        return new ZaiusRequest($zaiusClient, $method, $endpoint, $params);
    }

    protected function configureQueueProcessing(ZaiusClient $zaiusClient) 
    {
        $zaiusClient->setQueueDatabaseCredentials(
            [
            'driver' => DB_DRIVER,
            'host' => DB_HOST,
            'dbname' => DB_NAME,
            'user' => DB_USER,
            'password' => DB_PASSWORD,
            ],
            DB_TABLE
        );
    }
}