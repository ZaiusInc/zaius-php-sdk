<?php

namespace ZaiusSDK\Batch;

use ZaiusSDK\ZaiusResponse;

/**
 * Class ZaiusBatchResponse
 *
 * @package Zaius
 */
class ZaiusBatchResponse extends ZaiusResponse
{
    /**
     * @var ZaiusBatchRequest The original entity that made the batch request.
     */
    protected $batchRequest;

    /**
     * @var array An array of ZaiusResponse entities.
     */
    protected $responses = [];

    /**
     * Creates a new Response entity.
     *
     * @param ZaiusBatchRequest $batchRequest
     * @param ZaiusResponse     $response
     */
    public function __construct(
        ZaiusBatchRequest $batchRequest,
        ZaiusResponse $response
    ) {
        $this->batchRequest = $batchRequest;

        $request = $response->getRequest();
        $body = $response->getBody();
        $httpStatusCode = $response->getHttpStatusCode();
        $headers = $response->getHeaders();
        parent::__construct($request, $body, $httpStatusCode, $headers);

        $responses = $response->getDecodedBody();
        $this->setResponses($responses);
    }

    /**
     * Returns an array of ZaiusResponse entities.
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * The main batch response will be an array of requests so
     * we need to iterate over all the responses.
     *
     * @param array $responses
     */
    public function setResponses(array $responses)
    {
        $this->responses = [];

        foreach ($responses as $key => $response) {
            $this->addResponse($key, $response);
        }
    }

    /**
     * Add a response to the list.
     *
     * @param int        $key
     * @param array|null $response
     */
    public function addResponse($key, $response)
    {
        $originalRequestName = isset($this->batchRequest[$key]['name']) ? $this->batchRequest[$key]['name'] : $key;
        $originalRequest = isset($this->batchRequest[$key]['request']) ? $this->batchRequest[$key]['request'] : null;

        $httpResponseBody = isset($response['body']) ? $response['body'] : null;
        $httpResponseCode = isset($response['code']) ? $response['code'] : null;
        $httpResponseHeaders = isset($response['headers']) ? $this->normalizeBatchHeaders($response['headers']) : [];

        $this->responses[$originalRequestName] = new ZaiusResponse(
            $originalRequest,
            $httpResponseBody,
            $httpResponseCode,
            $httpResponseHeaders
        );
    }

    public function offsetSet($offset, $value)
    {
        $this->addResponse($offset, $value);
    }

    public function offsetExists($offset)
    {
        return isset($this->responses[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->responses[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->responses[$offset]) ? $this->responses[$offset] : null;
    }

    /**
     * Converts the batch header array into a standard format.
     *
     * @param array $batchHeaders
     *
     * @return array
     */
    private function normalizeBatchHeaders(array $batchHeaders)
    {
        $headers = [];

        foreach ($batchHeaders as $header) {
            $headers[$header['name']] = $header['value'];
        }

        return $headers;
    }
}
