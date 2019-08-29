<?php

namespace ZaiusSDK\Test\HttpClients;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use ZaiusSDK\HttpClients\GuzzleHttpClient;
use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\ZaiusRequest;

class GuzzleHttpClientTest extends TestAbstract
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

    public function testAsyncPoolRequest()
    {
        $this->configureQueueProcessing($this->zaiusClient);
        $privateKey = ZAIUS_PRIVATE_API_KEY;
        $client = new Client();
        $total = 50;
        $requests = function ($total) use ($client, $privateKey) {
            $uri = self::API_URL_V3.'/events';
            for ($i = 0; $i < $total; $i++) {
                yield function() use ($i, $privateKey, $client, $uri) {
                    return $client->postAsync(
                        $uri,
                        [
                            'headers' => ZaiusRequest::getDefaultHeaders($privateKey),
                            'json'    => [
                                [
                                    'type'        => 'product',
                                    'action'      => 'add_to_cart',
                                    'identifiers' => [
                                        'email' => 'tyler-' . $i . '@zaius.com'
                                    ],
                                    'data'        => ['product_id' => '123']
                                ],
                                [
                                    'type'        => 'product',
                                    'action'      => 'remove_from_cart',
                                    'identifiers' => ["email" => 'tyler-'.$i.'@zaius.com'],
                                    'data'        => ["product_id" => "123"]
                                ]
                            ]
                        ]
                    );
                };
            }
        };

        $pool = new Pool($client, $requests($total));

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();

        $this->assertEquals('fulfilled', $promise->getState());
        $this->assertNotInstanceOf(\ZaiusSDK\ZaiusException::class, $promise);
    }

    public function testConcurrentRequests()
    {
        $this->configureQueueProcessing($this->zaiusClient);
        $privateKey = ZAIUS_PRIVATE_API_KEY;
        $client = new Client();
        $total = 50;

        $uri = self::API_URL_V3.'/events';
        $requests = [];

        for ($i = 0; $i < $total; $i++) {

                $requests[$i] = $client->postAsync(
                    $uri,
                    [
                        'headers' => ZaiusRequest::getDefaultHeaders($privateKey),
                        'json'    => [
                            [
                                'type'        => 'product',
                                'action'      => 'add_to_cart',
                                'identifiers' => [
                                    'email' => 'tyler-' . $i . '@zaius.com'
                                ],
                                'data'        => ['product_id' => '123']
                            ],
                            [
                                'type'        => 'product',
                                'action'      => 'remove_from_cart',
                                'identifiers' => ["email" => 'tyler-'.$i.'@zaius.com'],
                                'data'        => ["product_id" => "123"]
                            ]
                        ]
                    ]
                );
        };

        // Wait for the requests to complete, even if some of them fail
        $results = \GuzzleHttp\Promise\settle($requests)->wait();

        $this->assertNotFalse($results);
        $this->assertEquals('fulfilled', $requests[0]->getState());
    }

}