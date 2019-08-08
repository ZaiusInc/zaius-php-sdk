<?php

namespace ZaiusSDK;

use ZaiusSDK\Zaius\Job;
use ZaiusSDK\Zaius\S3\S3Client;

/**
 * Class ZaiusClient
 * @package ZaiusSDK
 */
class ZaiusClient
{
    const MAX_BATCH_SIZE = 1000;

    /** @var string */
    protected $apiKey;

    /** @var string */
    protected $privateKey;

    /** @var int  */
    protected $timeout;

    const API_URL_V3 = 'http://api.zaius.com/v3';

    /**
     * ZaiusClient constructor.
     * @param string $apiKey
     * @param int $timeout
     */
    public function __construct($apiKey = '', $privateKey = '', $timeout = 30)
    {
        $this->apiKey = $apiKey;
        $this->privateKey = $privateKey;
        $this->timeout = $timeout;
    }

    /**
     * @param $customers
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postCustomer($customers, $queue=false)
    {
        $this->apiKey = $this->privateKey;
        $customers = $this->prepareForPost($customers);
        $realCustomers = array();
        foreach ($customers as $customer) {
            $realCustomers['attributes'] = $customer;
        }
        return $this->process($realCustomers, self::API_URL_V3.'/profiles', 'POST', $queue);
    }

    /**
     * @param $filters
     * @return array|null
     * @throws ZaiusException
     */
    public function getCustomer($filters)
    {
        $this->apiKey = $this->privateKey;
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
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postEvent($events, $queue=false)
    {
        $events = $this->prepareForPost($events);
        return $this->process($events, self::API_URL_V3.'/events', 'POST', $queue);
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
        $this->apiKey = $this->privateKey;
        return $this->process($list, self::API_URL_V3.'/lists', 'POST', $queue);
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
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function updateChannelOptIn($optedIn, $email, $queue=false)
    {
        $data = ['opted_in'=>$optedIn,'email'=>$email];
        return $this->process($data, self::API_URL_V3.'/lists/subscriptions', 'POST', $queue);
    }

    /**
     * @param $subscriptions
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function updateSubscription($subscriptions, $queue=false)
    {
        $subscriptions = $this->prepareForPost($subscriptions);
        return $this->process($subscriptions, self::API_URL_V3.'/lists/subscriptions', 'POST', $queue);
    }

    /**
     * @param array $objects
     * @param string $format
     * @param string $delimiter
     * @param bool $queue
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

        $this->apiKey = $this->privateKey;
        return $this->process($data, self::API_URL_V3.'/exports', 'POST', $queue, 'POST', $queue);
    }

    /**
     * @param array $select
     * @param string $format
     * @param string $delimiter
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function exportObjectsWithFilters($select = array(), $format = 'csv', $delimiter='comma', $queue=false)
    {
        $data = [];
        $data['select'] = $select;
        $data['format'] = $format;
        $data['delimiter'] = $delimiter;

        $this->apiKey = $this->privateKey;
        return $this->process($data, self::API_URL_V3.'/exports', 'POST', $queue);
    }

    /**
     * @param string $exportId
     * @return bool|string|null
     * @throws ZaiusException
     */
    public function getExportStatus($exportId)
    {
        $this->apiKey = $this->privateKey;
        return $this->get([], self::API_URL_V3.'/exports/'.$exportId);
    }

    /**
     * @param $requesterEmail
     * @param string $email
     * @param string $phone
     * @param string $vuid
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function optOut($requesterEmail, $email="", $phone="", $vuid="", $queue=false)
    {
        if (!$email && !$phone && !$vuid) {
            throw new ZaiusException("One of the email, phone or vuid fields must be specified");
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

        return $this->process($data, self::API_URL_V3.'/optout', 'POST', $queue);
    }

    /**
     * @return array
     * @throws ZaiusException
     */
    public function getObjects()
    {
        $this->apiKey = $this->privateKey;
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
        $this->apiKey = $this->privateKey;
        $data = $this->get([], self::API_URL_V3.'/schema/objects/'.$objectName);
        return json_decode($data, true);
    }

    /**
     * @param $name
     * @param $displayName
     * @param string $alias
     * @param $fields
     * @param $relations
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function createObjectSchema($name, $displayName, $alias='', $fields, $relations, $queue=false)
    {
        $this->apiKey = $this->privateKey;
        $data = [];
        $data['name'] = $name;
        $data['display_name'] = $displayName;
        $data['alias'] = $alias;
        $data['fields'] = $fields;
        $data['relations'] = $relations;

        return $this->process($data, self::API_URL_V3.'/schema/objects', 'POST', $queue);
    }

    /**
     * @param string $objectName
     * @return mixed
     * @throws ZaiusException
     */
    public function getObjectFields($objectName)
    {
        $this->apiKey = $this->privateKey;
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
        $this->apiKey = $this->privateKey;
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
     * @param string $description
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function createObjectField($objectName, $fieldName, $type, $displayName, $description='', $queue=false)
    {
        $this->apiKey = $this->privateKey;
        $data = [];
        $data['name'] = $fieldName;
        $data['type'] = $type;
        $data['display_name'] = $displayName;
        $data['description'] = $description;

        return $this->process($data, self::API_URL_V3.'/schema/objects/'.$objectName.'/fields', 'POST', $queue);
    }

    /**
     * @param string $objectName
     * @return null|array
     * @throws ZaiusException
     */
    public function getRelations($objectName)
    {
        $this->apiKey = $this->privateKey;
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
        $this->apiKey = $this->privateKey;
        $data = $this->get([], self::API_URL_V3.'/schema/objects/'.$objectName.'/relations/'.$relationName);
        return json_decode($data, true);
    }

    /**
     * @param $objectName
     * @param $data
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postObject($objectName, $data, $queue=false)
    {
        return $this->process($data, self::API_URL_V3.'/objects/'.$objectName, 'POST', $queue);
    }


    /**
     * @param $products
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postProduct($products, $queue=false)
    {
        $data = $this->prepareForPost($products);
        return $this->process($data, self::API_URL_V3.'/objects/products', 'POST', $queue);
    }

    /**
     * @param $orders
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function postOrder($orders, $queue=false)
    {
        $data = $this->prepareForPost($orders);
        return $this->process($data, self::API_URL_V3.'/objects/orders', 'POST', $queue);
    }


    /**
     * @param $objectName
     * @param $relations
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function createRelations($objectName, $relations, $queue=false)
    {
        $this->apiKey = $this->privateKey;
        return $this->process($relations, self::API_URL_V3.'/schema/objects/'.$objectName.'/relations', 'POST', $queue);
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
     * @param bool $queue
     * @return mixed
     * @throws ZaiusException
     */
    public function changeListName($listId, $newName, $queue=false)
    {
        $data = ['name'=>$newName];

        $this->apiKey = $this->privateKey;
        return $this->process($data, self::API_URL_V3.'/lists/'.$listId, 'PUT', $queue);
    }

    /**
     * @param $data
     * @return array
     */
    protected function prepareForPost($data)
    {
        if (!isset($data[0])) {
            $data = array($data);
        }

        return $data;
    }

    /**
     * @param $credentials
     * @param string $jobTable
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
     * @param $data
     * @param $url
     * @param $method
     * @param $queue
     * @return bool|mixed|string
     * @throws ZaiusException
     */
    protected function process($data, $url, $method, $queue)
    {
        if ($queue) {
            return \DJJob::enqueue(new Job($this->apiKey, $data, $url, $method));
        } else {
            return $this->post($data, $url, $method);
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
            throw new ZaiusException("Cannot post more than ".self::MAX_BATCH_SIZE.' objects');
        }
        $jsonData = json_encode($data);
        $length = strlen($jsonData);

        $curl = curl_init();

        $headers = array(
            'Content-Type: application/json',
            "Content-Length: $length",
        );
        if ($this->apiKey) {
            $headers[] = 'x-api-key: '.$this->apiKey;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $jsonData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout
        ));

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        if ($this->showException($result, $info)) {
            $error = curl_error($curl);
            throw new ZaiusException("Failed
             to $method to Zaius. Request: $url - $jsonData. Error: $error . Http code {$info['http_code']}. Raw response $result");
        }
        curl_close($curl);
        return json_decode($result, true);
    }

    /**
     * @param $parameters
     * @param $method
     * @param $url
     * @param bool $queue
     * @return bool|string|null
     * @throws ZaiusException
     */
    public function call($parameters, $method, $url, $queue = false)
    {
        if ($queue) {
            return \DJJob::enqueue(new Job($this->apiKey, $parameters, $url, $method));
        } else {
            $attempts = $this->removeAttempts($parameters, true);
            $parameters = $this->removeAttempts($parameters);

            $method = strtoupper($method);
            $curl = curl_init();

            $headers = array(
                'Content-Type: application/json'
            );
            if ($this->apiKey) {
                $headers[] = 'x-api-key: '.$this->apiKey;
            }

            if ($method == 'GET' && count($parameters)) {
                $url.="?".http_build_query($parameters);
            }

            $optionsArray = array(
                CURLOPT_URL            => $url,
                CURLOPT_CUSTOMREQUEST  => $method,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_CONNECTTIMEOUT => $this->timeout,
                CURLOPT_TIMEOUT => $this->timeout
            );

            if (in_array($method, ['POST','PUT'])) {
                $jsonData = json_encode($parameters);
                $length = strlen($jsonData);
                $headers[]= "Content-Length: $length";
                $optionsArray[CURLOPT_POSTFIELDS] = $jsonData;
            }

            curl_setopt_array($curl, $optionsArray);


            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            if ($this->showException($result, $info)) {
                $customErrorMessage = false;
                if ($info['http_code'] == 500) {
                    $retryLater = $this->retryLater(["+10 seconds", "+30 seconds", "+1 minute", "+5 minutes"], $attempts, $this->apiKey, $parameters, $url, $method);
                    if (!$retryLater) {
                        $customErrorMessage = 'FAILURE posting to Zaius, repeated 5xx error codes. No further attempts will be made. Raw request:'.$curl;
                    }
                }
                $error = curl_error($curl);
                curl_close($curl);
                throw new ZaiusException($this->zaiusExceptionMessage($customErrorMessage, $method, $error, $info, $result));
            }
            if ($info['http_code'] == 404) {
                $result = null;
            }

            curl_close($curl);
            return $result;
        }
    }

    /**
     * Remove the attempts from the parameters or
     * Return the attempts number
     *
     * @param $parameters
     * @param bool $returnAttempts
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
     * @param array $time
     * @param $apiKey
     * @param $parameters
     * @param $url
     * @param $method
     */
    private function retryLater(array $time, int $attempts, $apiKey, $parameters, $url, $method)
    {
        $i=0;
        $run_at = null;
        array_unshift($parameters, ['attempts'=>$attempts]);

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
        $this->enqueue(new Job($this->apiKey, $parameters, $url, $method), $run_at);

        return true;
    }

    /**
     * Return a custom or the default message
     *
     * @param bool $customErrorMessage
     * @param $method
     * @param $error
     * @param $info
     * @param $result
     * @return bool|string
     */
    private function zaiusExceptionMessage($customErrorMessage = false, $method, $error, $info, $result)
    {
        if (!$customErrorMessage) {
            return "Failed to {$method} from Zaius. Error: $error . Http code {$info['http_code']}. Raw response $result";
        }
        return $customErrorMessage;
    }
    /**
     * Enqueue a new Job
     *
     * @param $handler
     * @param null $run_at
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
     * @throws ZaiusException
     */
    protected function get($params, $url)
    {
        $curl = curl_init();

        $headers = array(
            'Content-Type: application/json'
        );
        if ($this->apiKey) {
            $headers[] = 'x-api-key: '.$this->apiKey;
        }

        if (count($params)) {
            $url.="?".http_build_query($params);
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout
        ));

        $result = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        if ($this->showException($result, $info)) {
            $error = curl_error($curl);
            throw new ZaiusException("Failed to GET from Zaius. Error: $error . Http code {$info['http_code']}. Raw response $result");
        }
        if ($info['http_code'] == 404) {
            return null;
        } else {
            return $result;
        }
    }

    /**
     * Check if it is not false, 200, 201, 202 or 404
     *
     * @param $result
     * @param $info
     * @return bool
     */
    private function showException($result, $info)
    {
        return ($result === false || ($info['http_code'] != 200 && $info['http_code'] != 201 && $info['http_code'] != 202 && $info['http_code'] != 404));
    }

    /**
     * Get date forward (e.g. $time = +5 minutes)
     *
     * @param null $time
     * @param string $format
     * @return false|string
     */
    private function get_date($time=null, $format='Y-m-d H:i:s')
    {
        if (empty($time)) {
            return date($format);
        }
        return date($format, strtotime($time));
    }
}
