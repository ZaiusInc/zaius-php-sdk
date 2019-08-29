<?php

namespace ZaiusSDK\HttpClients;

use GuzzleHttp\Pool;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use ZaiusSDK\ZaiusClient;
use ZaiusSDK\ZaiusException;
use ZaiusSDK\ZaiusRequest;

/**
 * Class GuzzleHttpClient
 * @package ZaiusSDK\HttpClients
 */
class GuzzleHttpClient
{
    /**
     * @var \GuzzleHttp\Client The Guzzle client.
     */
    protected $guzzleClient;

    /**
     * @param \GuzzleHttp\Client|null The Guzzle client.
     */
    public function __construct(
        Client $guzzleClient = null
    ) {
        $this->guzzleClient = $guzzleClient ?: new Client();
    }

//    public function sendAsync($requests){
//        if(is_array($requests) && $requests[0] instanceof Request){
//        }
//    }

    /**
     * Convert ZaiusBatch requests into Guzzle requests
     *
     * @param array $requests
     * @param       $apiKey
     *
     * @return array
     * @throws ZaiusException
     */
    public function convertRequests(array $requests, $apiKey)
    {
        $requestViaGuzzle = [];
        /** @var ZaiusRequest $r */
        foreach ($requests as $request => $r) {
            $method = $r->getMethod();
            $uri = $r->getUrl();
            $headers = ZaiusRequest::getDefaultHeaders($apiKey) ;
            $body = [];
            foreach ($r->getPostParams() as $obj => $event) {
                array_push($body, $event[0]);
            }
            $requestViaGuzzle[] = new \GuzzleHttp\Psr7\Request(
                $method,
                $uri,
                $headers,
                \GuzzleHttp\json_encode($body)
            );
        }

        return $requestViaGuzzle;
    }

    /**
     * @param $url
     * @param $method
     * @param $body
     * @param $headers
     * @param $timeout
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function send($url, $method, $body, $headers, $timeout)
    {
        $options = [
            'headers' => $headers,
            'body' => $body,
            'timeout' => $timeout,
            'connect_timeout' => $timeout,
        ];

        try {
            $rawResponse = $this->guzzleClient->request($method, $url, $options);
        } catch (\Exception $e) {
            $rawResponse = $e->getResponse();
            new ZaiusException($e->getMessage());
        }

        return $rawResponse->getBody()->getContents();
    }

    /**
     * Returns the Guzzle array of headers as a string.
     *
     * @param ResponseInterface $response The Guzzle response.
     *
     * @return string
     */
    public function getHeadersAsString($response)
    {
        $headers = $response->getHeaders();
        $rawHeaders = [];
        foreach ($headers as $name => $values) {
            $rawHeaders[] = $name . ": " . implode(", ", $values);
        }
        return implode("\r\n", $rawHeaders);
    }
}
