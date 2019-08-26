<?php

namespace ZaiusSDK\HttpClients;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

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
    public function __construct(Client $guzzleClient = null)
    {
        $this->guzzleClient = $guzzleClient ?: new Client();
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
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $rawResponse = $e->getResponse();
            new \ZaiusSDK\ZaiusException($e->getMessage());
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
