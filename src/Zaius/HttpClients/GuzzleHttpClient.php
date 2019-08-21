<?php

namespace ZaiusSDK\Zaius\HttpClients;

use GuzzleHttp\Client;
use ZaiusSDK\ZaiusException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Exception\RequestException;

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

    public function send($url, $method, $body, $headers, $timeout, $errorMsg)
    {
        $options = [
            'headers' => $headers,
            'body' => $body,
            'timeout' => $timeOut,
            'connect_timeout' => $timeOut,
        ];

        $request = $this->guzzleClient->createRequest($method, $url, $options);

        try {
            $response = $this->guzzleClient->send($request);
        } catch (RequestException $e) {
            $result = $e->getResponse();
            $error = $e->getMessage();
            $httpCode = $e->getCode();
            throw new ZaiusException(
                "Failed to {$method} to Zaius. Request: {$url} - {$body}. Error: {$error} . Http code {$httpCode}. Raw response {$result}"
            );
        }
        $rawHeaders = $this->getHeadersAsString($rawResponse);
        $rawBody = $rawResponse->getBody();
        $httpStatusCode = $rawResponse->getStatusCode();
        return $response;
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
