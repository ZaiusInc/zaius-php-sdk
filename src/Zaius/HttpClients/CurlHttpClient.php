<?php

namespace ZaiusSDK\Zaius\HttpClients;

use ZaiusSDK\ZaiusException;

/**
 * Class CurlHttpClient
 * @package ZaiusSDK\Zaius\HttpClients
 */
class CurlHttpClient
{

    /**
     * @var int The curl client error code
     */
    protected $curlErrorCode = 0;

    /**
     * @var string|boolean The raw response from the server
     */
    protected $rawResponse;

    /**
     * @var Curl Procedural curl as object
     */
    protected $curl;

    /**
     * CurlHttpClient constructor.
     * @param Curl|null $curl
     */
    public function __construct(Curl $curl = null)
    {
        $this->curl = $curl ?: new Curl();
    }

    /**
     * @param $url
     * @param $method
     * @param $body
     * @param array $headers
     * @param $timeOut
     * @return bool|mixed|string
     * @throws ZaiusException
     */
    public function send($url, $method, $body, array $headers, $timeOut)
    {
        $this->openConnection($url, $method, $body, $headers, $timeOut);
        $result = $this->sendRequest(true);

        $httpCode = $this->curl->getinfo(CURLINFO_HTTP_CODE);

        if ($this->showException($result, $httpCode)) {
            $error = $this->curl->error();
            throw new ZaiusException(
                "Failed to {$method} to Zaius. Request: {$url} - {$body}. Error: {$error} . Http code {$httpCode}. Raw response {$result}"
            );
        }

        $this->closeConnection();
        return $result;
    }

    /**
     * @param $url
     * @param $method
     * @param $body
     * @param array $headers
     * @return bool
     */
    public function sendAsync($url, $method, $body, array $headers)
    {
        $cmd = "curl -X ".$method;
        foreach ($this->compileRequestHeaders($headers) as $header) {
            $cmd .= " -H '".$header."'";
        }
        $cmd .= " -d '" . $body . "' " . "'" . $url . "'";
        $cmd .= " > /dev/null 2>&1 &";
        exec($cmd, $output, $exit);
        return $exit == 0;
    }

    /**
     * Opens a new curl connection.
     *
     * @param string $url     The endpoint to send the request to.
     * @param string $method  The request method.
     * @param string $body    The body of the request.
     * @param array  $headers The request headers.
     * @param int    $timeOut The timeout in seconds for the request.
     */
    public function openConnection($url, $method, $body, array $headers, $timeOut)
    {
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->compileRequestHeaders($headers),
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => $timeOut,
            CURLOPT_RETURNTRANSFER => true, // Return response as string
            CURLOPT_HEADER => true, // Enable header processing
        ];
        if ($method !== "GET") {
            $options[CURLOPT_POSTFIELDS] = $body;
        }
        $this->curl->init();
        $this->curl->setoptArray($options);
    }

    /**
     * Closes an existing curl connection
     */
    public function closeConnection()
    {
        $this->curl->close();
    }

    /**
     * Return the HTTP code
     */
    public function getHttpCode()
    {
        return $this->curl->getinfo(CURLINFO_HTTP_CODE);
    }

    /**
     * Return the Curl error
     */
    public function getCurlError()
    {
        return $this->curl->getinfo(CURLINFO_HTTP_CODE);
    }

    /**
     * Send the request and get the raw response from curl
     */
    public function sendRequest($return = false)
    {
        $this->rawResponse = $this->curl->exec();

        if ($return) {
            return $this->rawResponse;
        }
    }

    /**
     * Check if it is false or not a HTTP 200
     * or return the type (e.g. 404 will return 400)
     *
     * ToDo: Abstract to a new class
     *
     * @param $result
     * @param $info
     * @return bool
     */
    private function showException($result, $httpCode, $returnType = false)
    {
        if (!$result) {
            return false;
        }
        if (!$returnType) {
            return !($httpCode >= 200 && $httpCode < 300);
        }
        switch ($httpCode) {
            case ($httpCode < 200):
                return 100;
            case ($httpCode >= 200 && $httpCode < 300):
                return 200;
            case ($httpCode >= 300 && $httpCode < 400):
                return 300;
            case ($httpCode >= 400 && $httpCode < 500):
                return 400;
            case ($httpCode >= 500):
                return 500;
        }
    }

    /**
     * Compiles the request headers into a curl-friendly format.
     *
     * @param array $headers The request headers.
     *
     * @return array
     */
    public function compileRequestHeaders(array $headers)
    {
        $return = [];
        foreach ($headers as $key => $value) {
            $return[] = $key . ': ' . $value;
        }
        return $return;
    }

    /**
     * Extracts the headers and the body into a two-part array
     *
     * @return array
     */
    public function extractResponseHeadersAndBody()
    {
        $parts = explode("\r\n\r\n", $this->rawResponse);
        $rawBody = array_pop($parts);
        $rawHeaders = implode("\r\n\r\n", $parts);
        return [trim($rawHeaders), trim($rawBody)];
    }
}
