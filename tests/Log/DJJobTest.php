<?php

namespace ZaiusSDK\Test\Log;

use ZaiusSDK\Log\DJJob;
use ZaiusSDK\Test\TestAbstract;
use ZaiusSDK\Zaius\Worker;
use ZaiusSDK\ZaiusClient;
use ZaiusSDK\ZaiusException;

/**
 * Class DJJobTest
 *
 * @package ZaiusSDK\Test\Log
 */
class DJJobTest extends TestAbstract
{

    /**
     * @var ZaiusClient
     */
    private $zaiusClient;

    /**
     * @var DJJob
     */
    private $zaiusLog;


    /**
     * @inheritDoc
     */
    public function setUp()
    {
        $this->zaiusClient = $this->getZaiusClient(ZAIUS_PRIVATE_API_KEY);
        $this->zaiusLog = $this->getZaiusLog($this->zaiusClient);
        $this->configureQueueProcessing($this->zaiusClient);
    }

    /**
     * Testing getting errors summary json
     */
    public function testGetErrorsSummaryJson()
    {
        $this->createErrorsAndQueue(1, 1);
        $return = $this->zaiusLog->countAllErrors();

        $this->assertNotNull($return);
        $this->assertGreaterThan(0, $return);
    }

    /**
     * Testing counting all errors
     */
    public function testCountAllErrors()
    {
        $return = $this->zaiusLog->getErrorsSummaryJson();

        $this->assertNotNull($return);
        $this->assertJson($return);
    }

    /**
     * Testing counting errors from the last 24h
     */
    public function testCountErrors24h()
    {
        $this->createErrorsAndQueue(1, 0);
        $return = $this->zaiusLog->countErrors24h();

        $this->assertNotNull($return);
        $this->assertGreaterThan(0, $return);
    }

    /**
     * Testing counting errors from the last 1h
     */
    public function testCountErrors1h()
    {
        $this->createErrorsAndQueue(1, 0);
        $return = $this->zaiusLog->countErrors1h();

        $this->assertNotNull($return);
        $this->assertGreaterThan(0, $return);
    }

    /**
     * Testing getting the most recent error TS
     */
    public function testGetMostRecentErrorTs()
    {
        $this->createErrorsAndQueue(1, 1);
        $return = $this->zaiusLog->getMostRecentErrorTs();

        $this->assertNotNull($return);
        $this->assertNotFalse($return);
        $this->assertGreaterThan(0, $return);
    }

    /**
     * Creating errors and jobs queue
     *
     * @param $numberOfErrors
     * @param $numberOfJobs
     *
     * @throws ZaiusException
     */
    protected function createErrorsAndQueue($numberOfErrors, $numberOfJobs)
    {
        $errors = 1;
        while ($errors <= $numberOfErrors) {
            $events = [
                ['type' => 'error']
            ];
            $this->zaiusClient->postEvent($events, true);
            $errors++;
        }

        $worker = new Worker();
        $worker->processAll();

        $jobs = 1;
        while ($jobs <= $numberOfJobs) {
            $events = [
                [
                    'type' => 'product', 'action' => 'add_to_cart',
                    'identifiers' => ["email" => "tyler@zaius.com"],
                    'data' => ["product_id" => "123"]
                ],
                [
                    'type' => 'product', 'action' => 'remove_from_cart',
                    'identifiers' => ["email" => "tyler@zaius.com"],
                    'data' => ["product_id" => "123"]
                ]
            ];
            $this->zaiusClient->postEvent($events, true);
            $jobs++;
        }
    }
}
