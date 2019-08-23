<?php

namespace ZaiusSDK\Zaius\HttpClients;

use GuzzleHttp\Client;

/**
 * Class GuzzleHttpClient
 * @package ZaiusSDK\Zaius\HttpClients
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

        $request = $this->guzzleClient->createRequest($method, $url, $options);

        try {
            $rawResponse = $this->guzzleClient->send($request);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $rawResponse = $e->getResponse();
            new \ZaiusSDK\ZaiusException($e->getMessage());
        }

        $rawHeaders = $this->getHeadersAsString($rawResponse);
        $rawBody = $rawResponse->getBody();
        $httpStatusCode = $rawResponse->getStatusCode();
        return $httpStatusCode.$rawBody.$rawHeaders;
    }

    /**
     * Returns the Guzzle array of headers as a string.
     *
     * @param ResponseInterface $response The Guzzle response.
     *
     * @return string
     */
    public function getHeadersAsString(ResponseInterface $response)
    {
        $headers = $response->getHeaders();
        $rawHeaders = [];
        foreach ($headers as $name => $values) {
            $rawHeaders[] = $name . ": " . implode(", ", $values);
        }
        return implode("\r\n", $rawHeaders);
    }
}
