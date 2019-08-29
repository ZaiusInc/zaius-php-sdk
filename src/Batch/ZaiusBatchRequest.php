<?php

namespace ZaiusSDK\Batch;

use InvalidArgumentException;
use ZaiusSDK\ZaiusClient;
use ZaiusSDK\ZaiusException;
use ZaiusSDK\ZaiusRequest;

/**
 * Class BatchRequest
 */
class ZaiusBatchRequest
{
    /**
     * @var ZaiusClient
     */
    protected $app;
    /**
     * @var array
     */
    protected $requests = [];

    /**
     * Creates a new ZaiusBatchRequest entity.
     *
     * @param ZaiusClient $app
     * @param array       $requests
     */
    public function __construct(ZaiusClient $app, array $requests = [])
    {
        $this->app = $app;
        $this->add($requests);
    }

    /**
     * Adds a new request to the array.
     *
     * @param      $request
     * @param null $options
     *
     * @return ZaiusBatchRequest
     */
    public function add($request, $options = null)
    {
        if (is_array($request)) {
            foreach ($request as $key => $req) {
                $this->add($req, $key);
            }

            return $this;
        }

        if (!$request instanceof ZaiusRequest) {
            throw new InvalidArgumentException(
                'Argument for add() must be of type array or ZaiusRequest.'
            );
        }

        if (null === $options) {
            $options = [];
        } elseif (!is_array($options)) {
            $options = ['name' => $options];
        }

        $name = isset($options['name']) ? $options['name'] : null;

        unset($options['name']);

        $requestToAdd = [
            'name' => $name,
            'request' => $request,
            'options' => $options,
        ];

        $this->requests[] = $requestToAdd;

        return $this;
    }

    /**
     * Return the ZaiusRequest entities.
     *
     * @return array
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * Converts the requests into a JSON(P) string.
     *
     * @return string
     * @throws ZaiusException
     */
    public function convertRequestsToJson()
    {
        $requests = [];
        foreach ($this->requests as $request) {
            $options = [];

            if (null !== $request['name']) {
                $options['name'] = $request['name'];
            }

            $options += $request['options'];

            $requests[] = $this->requestEntityToBatchArray($request['request'], $options);
        }

        return json_encode($requests);
    }

    /**
     * Validate the request count before sending them as a batch.
     *
     * @throws ZaiusException
     */
    public function validateBatchRequestCount()
    {
        $batchCount = count($this->requests);
        if ($batchCount === 0) {
            throw new ZaiusException('There are no batch requests to send.');
        } elseif ($batchCount > ZaiusClient::MAX_BATCH_SIZE) {
            throw new ZaiusException(
                sprintf(
                    "You cannot send more than %s batch requests at a time.",
                    ZaiusClient::MAX_BATCH_SIZE
                )
            );
        }
    }

    /**
     * Converts a Request entity into an array that is batch-friendly.
     *
     * @param ZaiusRequest      $request The request entity to convert.
     * @param string|null|array $options Array of batch request options e.g. 'name'.
     *                                   If a string is given, it is the value of
     *                                   the 'name' option.
     *
     * @return array
     * @throws ZaiusException
     */
    public function requestEntityToBatchArray(ZaiusRequest $request, $options = null)
    {

        if (null === $options) {
            $options = [];
        } elseif (!is_array($options)) {
            $options = ['name' => $options];
        }

        $compiledHeaders = [];
        $headers = $request->getHeaders();
        foreach ($headers as $name => $value) {
            $compiledHeaders[] = $name . ': ' . $value;
        }

        $batch = [
            'headers' => $compiledHeaders,
            'method' => $request->getMethod(),
            'relative_url' => $request->getUrl(),
        ];

        // Since file uploads are moved to the root request of a batch request,
        // the child requests will always be URL-encoded.
        $body = $request->getUrlEncodedBody();
        if ($body) {
            $batch['body'] = $body;
        }

        $batch += $options;

        return $batch;
    }

    /**
     * @param $offset
     * @param $value
     */
    public function offsetSet($offset, $value)
    {
        $this->add($value, $offset);
    }

    /**
     * @param $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->requests[$offset]);
    }

    /**
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->requests[$offset]);
    }

    /**
     * @param $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->requests[$offset]) ? $this->requests[$offset] : null;
    }

    /**
     * Turn the requests into a POST with all data grouped by method and URL
     *
     * @TODO Split obj using the max batch limits
     * @param null $requests
     * @param bool $queue
     *
     * @return array
     * @throws ZaiusException
     */
    public function prepareBatchRequest($requests = null, $queue = false)
    {
        if (!is_array($requests)) {
            $requests = [];
            foreach ($this->getRequests() as $requestName => $request) {
                $requests[] = $request["request"];
            }
        }

        $batchRequests = [];
        $batchRequestsResult = [];

        foreach ($requests as $request) {
            if ($request instanceof ZaiusRequest) {
                $method = $request->getMethod();
                $url = $request->getUrl();
                $batchRequests[$method][$url][] = $request->getPostParams();
            }
        }
        foreach ($batchRequests as $method => $url) {
            foreach ($url as $endpoint => $params) {
                $batchRequestsResult[] = $this->app->request(
                    $method,
                    $endpoint,
                    $params,
                    $queue
                );
            }
        }

        return $batchRequestsResult;
    }
}
