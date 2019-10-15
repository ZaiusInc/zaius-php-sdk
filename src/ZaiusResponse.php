<?php

namespace ZaiusSDK;

use ZaiusSDK\ZaiusException;

/**
 * Class ZaiusResponse
 *
 * @package Zaius
 */
class ZaiusResponse
{
    /**
     * @var int The HTTP status code response from Zaius.
     */
    protected $httpStatusCode;

    /**
     * @var array The headers returned from Zaius.
     */
    protected $headers;

    /**
     * @var string The raw body of the response from Zaius.
     */
    protected $body;

    /**
     * @var array The decoded body of the Zaius response.
     */
    protected $decodedBody = [];

    /**
     * @var ZaiusRequest The original request that returned this response.
     */
    protected $request;

    /**
     * @var ZaiusException The exception thrown by this request.
     */
    protected $thrownException;

    /**
     * Creates a new Response entity.
     *
     * @param ZaiusRequest $request
     * @param string|null     $body
     * @param int|null        $httpStatusCode
     * @param array|null      $headers
     */
    public function __construct(ZaiusRequest $request, $body = null, $httpStatusCode = null, array $headers = [])
    {
        $this->request = $request;
        $this->body = $body;
        $this->httpStatusCode = $httpStatusCode;
        $this->headers = $headers;

        $this->decodeBody();
    }

    /**
     * Return the original request that returned this response.
     *
     * @return ZaiusRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Return the ZaiusClient entity used for this response.
     *
     * @return ZaiusClient
     */
    public function getApp()
    {
        return $this->request->getApp();
    }

    /**
     * Return the HTTP status code for this response.
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Return the HTTP headers for this response.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Return the raw body response.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Return the decoded body response.
     *
     * @return array
     */
    public function getDecodedBody()
    {
        return $this->decodedBody;
    }

    /**
     * Throws the exception.
     *
     * @throws ZaiusException
     */
    public function throwException()
    {
        throw $this->thrownException;
    }

    /**
     * Returns the exception that was thrown for this request.
     *
     * @return ZaiusException|null
     */
    public function getThrownException()
    {
        return $this->thrownException;
    }

    /**
     * Convert the raw response into an array if possible.
     */
    public function decodeBody()
    {
        $this->decodedBody = json_decode($this->body, true);
    }
}
