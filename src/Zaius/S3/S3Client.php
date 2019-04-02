<?php

namespace ZaiusSDK\Zaius\S3;

use Aws\S3\Exception\S3Exception;
use ZaiusSDK\ZaiusException;

/**
 * Class S3Client
 * @package ZaiusSDK\Zaius\S3
 */
class S3Client
{
    const ZAIUS_INCOMING = 'zaius-incoming';
    const ZAIUS_INCOMING_TMP = 'zaius-incoming-temp';

    /** @var string  */
    protected $trackerId;

    /** @var string  */
    protected $keyId;

    /** @var string  */
    protected $secretAccessKey;

    /**
     * S3Client constructor.
     * @param string $trackerId
     * @param string $keyId
     * @param string $secretAccessKey
     */
    public function __construct($trackerId, $keyId, $secretAccessKey)
    {
        $this->trackerId = $trackerId;
        $this->keyId = $keyId;
        $this->secretAccessKey = $secretAccessKey;
    }

    /**
     * @param $events
     * @param bool $storeInTmp
     * @throws ZaiusException
     */
    public function uploadEvents($events, $storeInTmp = false)
    {
        $this->uploadDataToS3($events, 'events', $storeInTmp);
    }

    /**
     * @param $customers
     * @param bool $storeInTmp
     * @throws ZaiusException
     */
    public function uploadCustomers($customers, $storeInTmp = false)
    {
        $this->uploadDataToS3($customers, 'customers', $storeInTmp);
    }

    /**
     * @param $products
     * @param bool $storeInTmp
     * @throws ZaiusException
     */
    public function uploadProducts($products, $storeInTmp = false)
    {
        $this->uploadDataToS3($products, 'products', $storeInTmp);
    }

    /**
     * @param $orders
     * @param bool $storeInTmp
     * @throws ZaiusException
     */
    public function uploadOrders($orders, $storeInTmp = false)
    {
        $this->uploadDataToS3($orders, 'orders', $storeInTmp);
    }

    /**
     * @param array $data
     * @param string $type
     * @throws ZaiusException
     */
    protected function validate($data, $type)
    {
        foreach ($data as $datum) {
            switch ($type) {
                case 'events':
                    $requiredFields = ['type' => '', 'action' => '', 'identifiers' => 'array', 'data' => 'array'];
                    break;
                case 'customers':
                    $requiredFields = ['attributes' => 'array'];
                    break;
                case 'orders':
                    $requiredFields = ['order' => 'array', 'identifiers' => 'array'];
                    break;
                case 'products':
                    $requiredFields = ['product_id' => ''];
                    break;
                default:
                    throw new ZaiusException("Unknown type");
            }
        }

        foreach ($data as $datum) {
            foreach ($requiredFields as $key => $value) {
                if (!isset($datum[$key])) {
                    throw new ZaiusException("Key $key must be defined");
                }
                if ($value == 'array' && !is_array($datum[$key])) {
                    ;
                    throw new ZaiusException("Key $key must contain an array");
                }
            }
        }

        return;
    }

    /**
     * Ensures a dataset is compatible before writing into S3.
     *
     * @param array $data
     * @param string $type
     * @param string $errormsg Optional error message for calling as a fallback to inbound validation.
     * @throws ZaiusException
     */
    protected function checkCompatibility($data, $type, $errormsg = '')
    {
        foreach ($data as $datum) {
            $requiredFields = ['type' => '', 'data' => 'array'];
            foreach ($requiredFields as $key => $value) {
                if (!isset($datum[$key])) {
                    throw new ZaiusException("Key $key must be defined on output.");
                }
                if ($value == 'array' && !is_array($datum[$key])) {;
                    throw new ZaiusException("Key $key must contain an array on output.");
                }
            }

            switch ($type) {
                case 'events':
                    $this->eventValidator($datum);
                    break;
                case 'customers':
                    $this->customerValidator($datum);
                    break;
                default:
                    throw new ZaiusException("Invalid passthrough. " . $errormsg);
            }
        }

        return;
    }

    /**
     * @param array $event
     * @throws ZaiusException
     */
    protected function eventValidator($event)
    {
        $data = $event["data"];
        $this->checkIdentifiers($data);
        $etype = $event["type"];
        switch ($etype) {
            case 'pageview':
                break;
            case 'list':
                $list_id = $data["list_id"];
                $email = $data["email"];
                $action = $data["action"];
                if (empty($list_id)) {
                    throw new ZaiusException("S3-bound list events must specify data.list_id");
                }
                if (empty($email)) {
                    throw new ZaiusException("S3-bound list events must specify data.email");
                }
                if (empty($action)) {
                    throw new ZaiusException("S3-bound list events must specify data.action");
                }
                if ($action != "unsubscribe" && $action != "subscribe") {
                    throw new ZaiusException("S3-bound list events must have data.action of [un]subscribe");
                }
                break;
            case 'product':
                $product_id = $data["product_id"];
                if (empty($product_id)) {
                    throw new ZaiusException("S3-bound product events must have data.product_id");
                }
                break;
            case 'order':
                if (!is_array($data["order"])) {
                    throw new ZaiusException("S3-bound order events must have an array on data.order");
                }
                $order_id = $data["order"]["order_id"];
                if (empty($order_id)) {
                    throw new ZaiusException("S3-bound order events must have data.order.order_id");
                }
                break;
            default:
                // Arbitrary Events with type (checked here) and an identifier (checked earlier) are acceptable.
                if (empty($etype)) {
                    throw new ZaiusException("S3-bound events must have 'type' specified.");
                }
                break;
        }
        return;
    }

    /**
     * Validates a customer outbound to S3.
     *
     * @param array $customer
     */
    protected function customerValidator($customer)
    {
        $ctype = $customer["type"];
        if ($ctype != "customer") {
            throw new ZaiusException("S3-bound customers must have 'type' of 'customer'");
        }
        $this->checkIdentifiers($customer["data"]);
        return;
    }

    /**
     * Allows us to confirm that at least one valid identifier is present when needed.
     *
     * @param array $data
     * @throws ZaiusException
     */
    protected function checkIdentifiers($data)
    {
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'customer_id':
                case 'email':
                case 'vuid':
                    return;
                default:
                    if (preg_match("/^zaius_alias_\w+/", $key) || preg_match("/\w+_push_tokens$/", $key)) {
                        return;
                    }
                    break;
            }
        }
        throw new ZaiusException("No identifier found.");
    }

    /**
     * @param array $data
     * @param string $type
     * @param bool $storeInTmp
     * @return \Aws\Result
     * @throws ZaiusException
     */
    protected function uploadDataToS3($data, $type, $storeInTmp = false)
    {

        $needsTranslation = true;
        try {
            $this->validate($data, $type);
        } catch (ZaiusException $e) {
            $errormsg = $e->getMessage();
            if ($errormsg == "Unknown Type") {
                throw $e;
            } else {
                $this->checkCompatibility($data, $type, $errormsg);
                $needsTranslation = false;
            }
        }

        $s3Translator = new S3Translator();
        switch ($type) {
            case 'events':
                if ($needsTranslation) {
                    $translatedData = $s3Translator->translateEvents($data);
                } else {
                    $translatedData = $data;
                }
                $s3Extension = 'events.zaius';
                break;
            case 'customers':
                if ($needsTranslation) {
                    $translatedData = $s3Translator->translateCustomers($data);
                } else {
                    $translatedData = $data;
                }
                $s3Extension = 'customers.zaius';
                break;
            case 'orders':
                $translatedData = $s3Translator->translateOrders($data);
                $s3Type = 'order';
                $s3Extension = 'orders.zaius';
                break;
            case 'products':
                $translatedData = $s3Translator->translateProducts($data);
                $s3Type = 'product';
                $s3Extension = 'products.zaius';
                break;
            default:
                throw new ZaiusException("Invalid S3 type");
        }

        $s3 = new \Aws\S3\S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'credentials' => [
                'key' => $this->keyId,
                'secret' => $this->secretAccessKey,
            ],
        ]);

        $jsonBody = '';
        foreach ($translatedData as $datum) {
            if ($type == 'events' || $type == 'customers') {
                $tmp = $datum;
            } else {
                $tmp = ['type' => $s3Type, 'data' => $datum];
            }
            $jsonBody .= json_encode($tmp) . "\n";
        }

        if ($storeInTmp) {
            $bucket = self::ZAIUS_INCOMING_TMP;
        } else {
            $bucket = self::ZAIUS_INCOMING;
        }

        $ret = $s3->putObject([
            'Bucket' => $bucket,
            'Key' => $key = $this->trackerId . '/' . date('Y-m-d-H-i-s') . '.' . $s3Extension,
            'Body' => $jsonBody,
        ]);
        return $ret;
    }
}
