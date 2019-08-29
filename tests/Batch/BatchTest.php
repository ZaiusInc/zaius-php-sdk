<?php

namespace ZaiusSDK\Test\Batch;

use ZaiusSDK\Batch\ZaiusBatchRequest;
use ZaiusSDK\HttpClients\GuzzleHttpClient;
use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Worker;

class BatchTest extends TestAbstract
{

    /**
     * @var \ZaiusSDK\ZaiusClient
     */
    private $zaiusClient;
    /**
     * @var GuzzleHttpClient
     */
    private $guzzleHttpClient;

    public function setUp()
    {
        $this->zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $this->guzzleHttpClient = $this->getGuzzleHttpClient();
    }

    public function testBatchPostEventsViaGuzzle()
    {
        $this->configureQueueProcessing($this->zaiusClient);
        $batchRequest = new ZaiusBatchRequest($this->zaiusClient);

        $batchRequest->add(
            $this->zaiusClient->request(
                'POST',
                self::API_URL_V3.'/events',
                [
                    'type' => 'product',
                    'action' => 'add_to_cart',
                    'identifiers' => ['email' => 'tyler@zaius.com'],
                    'data' => ['product_id' => '123']
                ])
        );

        $batchRequest->add(
            $this->zaiusClient->request(
                'POST',
                self::API_URL_V3.'/events',
                [
                    'type' => 'product',
                    'action' => 'remove_from_cart',
                    'identifiers' => ["email" => "tyler@zaius.com"],
                    'data' => ["product_id" => "123"]
                ]
            )
        );

        $requests = $batchRequest->prepareBatchRequest();
        $ret = $this->zaiusClient->callAsync($requests);
        $this->assertNotFalse($ret);
        $this->assertNotInstanceOf(\ZaiusSDK\ZaiusException::class, $ret);
    }

    public function testArrayBatchEvents()
    {
        $this->configureQueueProcessing($this->zaiusClient);
        $events = [
            [
                'type' => 'product',
                'action' => 'add_to_cart',
                'identifiers' => ["email" => "tyler@zaius.com"],
                'data' => ["product_id" => "123"]
            ],
            [
                'type' => 'product',
                'action' => 'remove_from_cart',
                'identifiers' => ["email" => "tyler@zaius.com"],
                'data' => ["product_id" => "123"]
            ]
        ];

        /**
         * Returns the last inserted ID or false if enqueuing failed
        */
        $ret = $this->zaiusClient->postEvent($events, true);
        $this->assertNotFalse($ret);
        $this->assertGreaterThan(0, $ret);

        $worker = new Worker();
        $worker->processAll();
    }

}