<?php

namespace ZaiusSDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Response;
use ZaiusSDK\HttpClients\GuzzleHttpClient;
use ZaiusSDK\Zaius\Job;
use ZaiusSDK\Zaius\S3\S3Client;
use ZaiusSDK\HttpClients\CurlHttpClient;

/**
 * Class ZaiusClient
 *
 * @package ZaiusSDK
 */
class ZaiusClient
{
    const MAX_BATCH_SIZE = 1000;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var int
     */
    protected $timeout;

    const API_URL_V3 = 'http://api.zaius.com/v3';

    /**
     * ZaiusClient constructor.
     *
     * @param string $apiKey
     * @param string $privateKey
     * @param int    $timeout
     */
    public function __construct($apiKey = '', $privateKey = '', $timeout = 30)
    {
        $this->apiKey = !isset($privateKey) ? $privateKey : $apiKey;
        $this->timeout = $timeout;
    }

    /**
     * @param $customers
     * @param bool      $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postCustomer($customers, $queue=false)
    {
        $realCustomers = [];
        $customers = $this->prepareForPost($customers);
        foreach ($customers as $customer) {
            $realCustomers['attributes'] = $customer;
        }

        $request = $this->request(
            'POST',
            self::API_URL_V3.'/profiles',
            $realCustomers,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param $filters
     * @return array|null
     * @throws ZaiusException
     */
    public function getCustomer($filters)
    {
        $result = $this->get($filters, self::API_URL_V3.'/profiles');
        if ($result === null) {
            return $result;
        } else {
            $data = json_decode($result, true);
            return $data;
        }
    }

    /**
     * @param $events
     * @param bool   $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postEvent($events, $queue=false)
    {
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/events',
            $events,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param $list
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function createList($list, $queue=false)
    {
        if (!isset($list['name'])) {
            throw new ZaiusException("You must specify the name of the list");
        }
        
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/lists',
            $list,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @return array
     * @throws ZaiusException
     */
    public function getLists()
    {
        $data = $this->get([], self::API_URL_V3.'/lists');
        return json_decode($data, true);
    }

    /**
     * @param $filters
     * @return bool|mixed|string|null
     * @throws ZaiusException
     */
    public function getSubscriptions($filters)
    {
        $result = $this->get($filters, self::API_URL_V3.'/lists/subscriptions');
        if ($result === null) {
            return $result;
        } else {
            $data = json_decode($result, true);
            return $data;
        }
    }

    /**
     * @param $optedIn
     * @param $email
     * @param bool    $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function updateChannelOptIn($optedIn, $email, $queue=false)
    {
        $data = ['opted_in'=>$optedIn,'email'=>$email];
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/lists/subscriptions',
            $data,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param $subscriptions
     * @param bool          $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function updateSubscription($subscriptions, $queue=false)
    {
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/lists/subscriptions',
            $subscriptions,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param array  $objects
     * @param string $format
     * @param string $delimiter
     * @param bool   $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function exportObjects($objects = array(), $format='csv', $delimiter='comma', $queue=false)
    {
        $data = [];
        if (count($objects)) {
            $data['objects'] = $objects;
        }
        $data['format'] = $format;
        $data['delimiter'] = $delimiter;

        $request = $this->request(
            'POST',
            self::API_URL_V3.'/exports',
            $data,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param array  $select
     * @param string $format
     * @param string $delimiter
     * @param bool   $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function exportObjectsWithFilters(
        $select = [],
        $format = 'csv',
        $delimiter='comma',
        $queue=false
    ) {
        $data = [
            'select' => $select,
            'format' => $format,
            'delimiter' => $delimiter
        ];

        $request = $this->request(
            'POST',
            self::API_URL_V3.'/exports',
            $data,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param string $exportId
     * @return bool|string|null
     * @throws ZaiusException
     */
    public function getExportStatus($exportId)
    {
        return $this->get([], self::API_URL_V3.'/exports/'.$exportId);
    }

    /**
     * @param $requesterEmail
     * @param string         $email
     * @param string         $phone
     * @param string         $vuid
     * @param bool           $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function optOut(
        $requesterEmail,
        $email="",
        $phone="",
        $vuid="",
        $queue=false
    ) {
        if (!$email && !$phone && !$vuid) {
            throw new ZaiusException(
                "One of the email, phone or vuid fields must be specified"
            );
        }

        $data = [];
        $data['requester'] = $requesterEmail;
        if ($email) {
            $data['email'] = $email;
        }
        if ($phone) {
            $data['phone'] = $phone;
        }
        if ($vuid) {
            $data['vuid'] = $vuid;
        }

        $request = $this->request(
            'POST',
            self::API_URL_V3.'/optout',
            $data,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @return array
     * @throws ZaiusException
     */
    public function getObjects()
    {
        $data = $this->get([], self::API_URL_V3.'/schema/objects');
        return json_decode($data, true);
    }

    /**
     * @param string $objectName
     * @return array|mixed|null
     * @throws ZaiusException
     */
    public function getObject($objectName)
    {
        $data = $this->get([], self::API_URL_V3.'/schema/objects/'.$objectName);
        return json_decode($data, true);
    }

    /**
     * @param $name
     * @param $displayName
     * @param string      $alias
     * @param $fields
     * @param $relations
     * @param bool        $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function createObjectSchema(
        $name,
        $displayName,
        $alias='',
        $fields='',
        $relations='',
        $queue=false
    ) {
        $data = [
             'name' => $name,
             'display_name' => $displayName,
             'alias' => $alias,
             'fields' => $fields,
             'relations' => $relations,
         ];

        $request = $this->request(
            'POST',
            self::API_URL_V3.'/schema/objects',
            $data,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param string $objectName
     * @return mixed
     * @throws ZaiusException
     */
    public function getObjectFields($objectName)
    {
        $data = $this->get([], self::API_URL_V3.'/schema/objects/'.$objectName.'/fields');
        return json_decode($data, true);
    }

    /**
     * @param string $objectName
     * @param string $fieldName
     * @return mixed
     * @throws ZaiusException
     */
    public function getObjectField($objectName, $fieldName)
    {
        $data = [];
        $data['object_name'] = $objectName;
        $data['field_name'] = $fieldName;

        $data = $this->get([], self::API_URL_V3.'/schema/objects/'.$objectName.'/fields/'.$fieldName);
        return json_decode($data, true);
    }

    /**
     * @param $objectName
     * @param $fieldName
     * @param $type
     * @param $displayName
     * @param string      $description
     * @param bool        $queue
     * @return mixed
     */
    public function createObjectField(
        $objectName,
        $fieldName,
        $type,
        $displayName,
        $description='',
        $queue=false
    ) {
        $data = [
            'name' => $fieldName,
            'type' => $type,
            'display_name' => $displayName,
            'description' => $description,
        ];

        $request = $this->request(
            'POST',
            self::API_URL_V3.'/schema/objects/'.$objectName.'/fields',
            $data,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param string $objectName
     * @return null|array
     * @throws ZaiusException
     */
    public function getRelations($objectName)
    {
        $data = $this->get([], self::API_URL_V3.'/schema/objects/'.$objectName.'/relations');
        return json_decode($data, true);
    }

    /**
     * @param string $objectName
     * @param string $relationName
     * @return array|null
     * @throws ZaiusException
     */
    public function getRelation($objectName, $relationName)
    {
        $data = $this->get([], self::API_URL_V3.'/schema/objects/'.$objectName.'/relations/'.$relationName);
        return json_decode($data, true);
    }

    /**
     * @param $objectName
     * @param $data
     * @param bool       $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postObject($objectName, $data, $queue=false)
    {
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/objects/'.$objectName,
            $data,
            $queue
        );

        return $this->process($request);
    }


    /**
     * @param $products
     * @param bool     $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postProduct($products, $queue=false)
    {
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/objects/products',
            $products,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param $orders
     * @param bool   $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postOrder($orders, $queue=false)
    {
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/objects/orders',
            $orders,
            $queue
        );

        return $this->process($request);
    }


    /**
     * @param $objectName
     * @param $relations
     * @param bool       $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function createRelations($objectName, $relations, $queue=false)
    {
        $request = $this->request(
            'POST',
            self::API_URL_V3.'/schema/objects/'.$objectName.'/relations',
            $relations,
            $queue
        );

        return $this->process($request);
    }


    /**
     * @param string $trackerId
     * @param string $keyId
     * @param string $secretAccessKey
     * @return S3Client
     */
    public function getS3Client($trackerId, $keyId, $secretAccessKey)
    {
        return new S3Client($trackerId, $keyId, $secretAccessKey);
    }

    /**
     * @param $listId
     * @param $newName
     * @param bool    $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function changeListName($listId, $newName, $queue=false)
    {
        $data = ['name'=>$newName];
        $request = $this->request(
            'PUT',
            self::API_URL_V3.'/lists/'.$listId,
            $data,
            $queue
        );

        return $this->process($request);
    }

    /**
     * @param $data
     * @return array
     * @throws ZaiusException
     */
    protected function prepareForPost($data)
    {
        if (!isset($data[0])) {
            $data = array($data);
        }

        if (count($data) > self::MAX_BATCH_SIZE) {
            throw new ZaiusException(
                "Cannot post more than ".self::MAX_BATCH_SIZE.' objects'
            );
        }

        return $data;
    }

    /**
     * @param $credentials
     * @param string      $jobTable
     */
    public function setQueueDatabaseCredentials($credentials, $jobTable='')
    {
        if ($jobTable) {
            \DJJob::configure($credentials, $jobTable);
        } else {
            \DJJob::configure($credentials);
        }
    }

    /**
     * @param ZaiusRequest $request
     * @return bool|mixed|string
     */
    protected function process(ZaiusRequest $request)
    {
        try {
            $data = $request->getParams();
            $url = $request->getUrl();
            $method = $request->getMethod();

            if ($request->isQueue()) {
                return $this->enqueue(
                    new Job($this->apiKey, $data, $url, $method)
                );
            }
            return $this->post($data, $url, $method);
        } catch (ZaiusException $exception) {
            return $exception->getMessage();
        }
    }

    /**
     * @param $data
     * @param $url
     * @param string $method
     * @return mixed
     * @throws ZaiusException
     */
    protected function post($data, $url, $method = 'POST')
    {
        if (count($data) > self::MAX_BATCH_SIZE) {
            throw new ZaiusException(
                "Cannot post more than ".self::MAX_BATCH_SIZE.' objects'
            );
        }

        $jsonData = json_encode($data);

        $headers = ZaiusRequest::getDefaultHeaders($this->apiKey, $data);
        
        $curl = new CurlHttpClient();
        $result = $curl->sendAsync($url, $method, $jsonData, $headers);


        return $result;
    }

    /**
     * Execute many requests
     *
     * @param array $requests
     *
     * @throws ZaiusException
     */
    public function callAsync(array $requests)
    {
        $guzzleHttpClient = new GuzzleHttpClient();
        $guzzleRequests = $guzzleHttpClient->convertRequests(
            $requests,
            $this->apiKey
        );

        $pool = new Pool(new Client, $guzzleRequests, [
            'concurrency' => 10,
            'fulfilled' => function ($response, $index) {
                // this is delivered each successful response
            },
            'rejected' => function ($reason, $index) {
                // this is delivered each failed request
                $result = $reason->getMessage();
                $httpCode = $reason->getCode();
                throw new ZaiusException($result, $httpCode);
            }
        ]);

        // Initiate the transfers and create a promise
        $promise = $pool->promise();

        // Force the pool of requests to complete.
        $promise->wait();
    }

    /**
     * @param $parameters
     * @param $method
     * @param $url
     * @param bool       $queue
     * @return bool|string|null
     * @throws ZaiusException
     */
    public function call($parameters, $method, $url, $queue = false)
    {
        if ($queue) {
            return $this->enqueue(
                new Job($this->apiKey, $parameters, $url, $method)
            );
        } else {
            $attempts = $this->removeAttempts($parameters, true);
            $parameters = $this->removeAttempts($parameters);

            $method = strtoupper($method);

            $headers = ZaiusRequest::getDefaultHeaders($this->apiKey);

            if ($method == 'GET' && count($parameters)) {
                $url.="?".http_build_query($parameters);
            }

            $jsonData = '';
            if (in_array($method, ['POST','PUT'])) {
                $jsonData = json_encode($parameters);
                $length = strlen($jsonData);
                $headers = array_merge(['Content-Length' => $length], $headers);
            }

            /* @var CurlHttpClient $curl */
            $curl = new CurlHttpClient();
            $curl->openConnection(
                $url,
                $method,
                $jsonData,
                $headers,
                $this->timeout
            );
            $result = $curl->sendRequest();
            $httpCode = $curl->getHttpCode();
            $curlReturnBody = $curl->extractResponseHeadersAndBody()[1];
            $expectionMsg = "Failed to {$method} from Zaius.";

            if ($this->showException($result, $httpCode)) {
                $showException = true;
                $expection = [];
                if ($httpCode >= 500) {
                    $retryLater = $this->retryLater(
                        ["+10 seconds", "+30 seconds", "+1 minute", "+5 minutes"],
                        $attempts,
                        $this->apiKey,
                        $parameters,
                        $url,
                        $method
                    );
                    if (!$retryLater) {
                        $expectionMsg = 'FAILURE posting to Zaius, repeated 5xx error codes. No further attempts will be made.';
                    } else {
                        $showException = false;
                    }
                }

                $error = $curl->getCurlError();
                $expection['method'] = $method;
                $expection['error'] = $error;
                $expection['http_code'] = $httpCode;
                $expection['body'] = $curlReturnBody;
                $expection['raw_return'] = $result;

                if ($showException) {
                    $curl->closeConnection();
                    throw new ZaiusException(
                        $expectionMsg.
                        ' Returned: '. $expection['body'].
                        ' Raw return: '. $expection['raw_return'],
                        $expection['error']
                    );
                }
            }

            $curl->closeConnection();

            return $curlReturnBody;
        }
    }

    /**
     * Remove the attempts from the parameters or
     * Return the attempts number
     *
     * @param  $parameters
     * @param  bool       $returnAttempts
     * @return int|mixed
     */
    private function removeAttempts($parameters, $returnAttempts = false)
    {
        $attempts = 0;
        if (isset($parameters[0])) {
            if (array_key_exists('attempts', $parameters[0])) {
                $attempts = $parameters[0]['attempts'];
                array_shift($parameters);
            }
        }
        return ($returnAttempts) ? $attempts : $parameters;
    }

    /**
     * Set when retry the same Job
     *
     * @param  array      $time
     * @param  int        $attempts
     * @param  $apiKey
     * @param  $parameters
     * @param  $url
     * @param  $method
     * @return bool
     */
    private function retryLater(array $time, $attempts, $apiKey, $parameters, $url, $method)
    {
        $i=0;
        $run_at = null;

        if ($attempts > count($time)) {
            return false;
        }

        while ($i < count($time)) {
            if ($i == $attempts) {
                $run_at = $this->get_date($time[$i], "Y-m-d H:i:s");
                break;
            }
            $i++;
        }

        $attempts++;
        array_unshift($parameters, ['attempts'=>$attempts]);
        $this->enqueue(new Job($apiKey, $parameters, $url, $method), $run_at);

        return true;
    }

    /**
     * Enqueue a new Job
     *
     * @param  $handler
     * @param  null    $run_at
     * @return bool|string
     */
    private function enqueue($handler, $run_at = null)
    {
        return \DJJob::enqueue($handler, $queue = "default", $run_at);
    }

    /**
     * @param $params
     * @param $url
     * @return bool|string|null
     * @throws GuzzleException
     */
    protected function get($params, $url)
    {
        $headers = ZaiusRequest::getDefaultHeaders($this->apiKey);

        if (count($params)) {
            $url.="?".http_build_query($params);
        }

        $request = new GuzzleHttpClient();
        $result = $request->send(
            $url,
            'GET',
            '',
            $headers,
            $this->timeout
        );

        return $result;
    }

    /**
     * Check if it is false or not a HTTP 200
     * or return the type (e.g. 404 will return 400)
     *
     * ToDo: Abstract to a new class
     *
     * @param  $result
     * @param  $info
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
            case ($httpCode < 200): return 100;
            case ($httpCode >= 200 && $httpCode < 300): return 200;
            case ($httpCode >= 300 && $httpCode < 400): return 300;
            case ($httpCode >= 400 && $httpCode < 500): return 400;
            case ($httpCode >= 500): return 500;
        }
        return true;
    }

    /**
     * Get date forward (e.g. $time = +5 minutes)
     *
     * @param  null   $time
     * @param  string $format
     * @return false|string
     */
    private function get_date($time=null, $format='Y-m-d H:i:s')
    {
        if (empty($time)) {
            return date($format);
        }
        return date($format, strtotime($time));
    }

    /**
     * @param       $method
     * @param       $endpoint
     * @param array $params
     * @param bool  $queue
     *
     * @return ZaiusRequest
     * @throws ZaiusException
     */
    public function request($method, $endpoint, array $params = [], $queue = false)
    {
        $params = $this->prepareForPost($params);
        return new ZaiusRequest($this, $method, $endpoint, $params, $queue);
    }
}
