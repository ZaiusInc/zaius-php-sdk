<?php

namespace ZaiusSDK\Zaius;

use ZaiusSDK\ZaiusClient;

class Job {

    protected $apiKey;

    protected $data;

    protected $url;

    protected $method;

    /**
     * Job constructor.
     * @param $apiKey
     * @param $data
     * @param $url
     * @param $method
     */
    public function __construct($apiKey,$data,$url,$method)
    {
        $this->apiKey = $apiKey;
        $this->data = $data;
        $this->url = $url;
        $this->method = $method;
    }

    /**
     * @return bool|string|null
     * @throws \ZaiusSDK\ZaiusException
     */
    public function perform() {
        $zaiusClient = new ZaiusClient($this->apiKey);
        return $zaiusClient->call($this->data,$this->method,$this->url);
    }
}