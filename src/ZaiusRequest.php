<?php

namespace ZaiusSDK;

use ZaiusSDK\Url\ZaiusUrlManipulator;

/**
 * Class ZaiusRequest
 *
 * @package Zaius
 */
class ZaiusRequest
{
    protected $app;
    protected $method;
    protected $endpoint;
    protected $headers = [];
    protected $params = [];
    protected $queue = false;

    /**
     * Creates a new Request entity.
     *
     * @param ZaiusClient|null $app
     * @param null $method
     * @param null $endpoint
     * @param array $params
     * @param bool $queue
     */
    public function __construct(
        ZaiusClient $app = null,
        $method = null,
        $endpoint = null,
        array $params = [],
        $queue = false
    ) {
        $this->setApp($app);
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setParams($params);
        $this->setQueue($queue);
    }

    /**
     * Set the ZaiusClient entity used for this request.
     *
     * @param ZaiusClient|null $app
     */
    public function setApp(ZaiusClient $app = null)
    {
        $this->app = $app;
    }

    /**
     * Return the ZaiusClient entity used for this request.
     *
     * @return \ZaiusSDK\ZaiusClient
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Set the HTTP method for this request.
     *
     * @param string
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
    }

    /**
     * Return the HTTP method for this request.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Validate that the HTTP method is set.
     *
     * @throws ZaiusException
     */
    public function validateMethod()
    {
        if (!$this->method) {
            throw new ZaiusException('HTTP method not specified.');
        }

        if (!in_array($this->method, ['GET', 'POST', 'PUT'])) {
            throw new ZaiusException('Invalid HTTP method specified.');
        }
    }

    /**
     * Set the endpoint for this request.
     *
     * @param string
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * Return the endpoint for this request.
     *
     * @return string
     */
    public function getEndpoint()
    {
        // For batch requests, this will be empty
        return $this->endpoint;
    }

    /**
     * Generate and return the headers for this request.
     *
     * @return array
     */
    public function getHeaders()
    {
        $headers = static::getDefaultHeaders();

        return array_merge($this->headers, $headers);
    }

    /**
     * Set the headers for this request.
     *
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * Set the params for this request.
     *
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $this->params = $params;
    }

    /**
     * Returns the body of the request as URL-encoded.
     *
     */
    public function getUrlEncodedBody()
    {
        $params = $this->getPostParams();

        return http_build_query($params, null, '&');
    }

    /**
     * Generate and return the params for this request.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Only return params on POST requests.
     *
     * @return array
     */
    public function getPostParams()
    {
        if ($this->getMethod() === 'POST') {
            return $this->getParams();
        }

        return [];
    }

    /**
     * Generate and return the URL for this request.
     *
     * @return string
     * @throws ZaiusException
     */
    public function getUrl()
    {
        try {
            $this->validateMethod();
        } catch (ZaiusException $e) {
            throw new ZaiusException($e->getMessage());
        }

        $url = $this->getEndpoint();

        if ($this->getMethod() !== 'POST') {
            $params = $this->getParams();
            $url = ZaiusUrlManipulator::appendParamsToUrl($url, $params);
        }

        return $url;
    }

    /**
     * Return the default headers that every request should use.
     *
     * @param null $apiKey
     *
     * @param      $params
     *
     * @return array
     */
    public static function getDefaultHeaders($apiKey = null, $params = null)
    {
        $headers = [
            'Content-Type'    => 'application/json',
            'Accept-Encoding' => '*'
        ];

        if (isset($params)) {
            $jsonData = json_encode($params);
            $length = strlen($jsonData);
            $headers = array_merge(['Content-Length' => $length], $headers);
        }

        if (isset($apiKey)) {
            $headers = array_merge(['x-api-key' => $apiKey], $headers);
        }

        return $headers;
    }

    /**
     * @return bool
     */
    public function isQueue()
    {
        return $this->queue;
    }

    /**
     * @param bool $queue
     */
    public function setQueue($queue)
    {
        $this->queue = $queue;
    }
}
