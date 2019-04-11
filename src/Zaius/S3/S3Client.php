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
    public function __construct($trackerId,$keyId,$secretAccessKey)
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
    public function uploadEvents($events,$storeInTmp = false, $prefix = null) {
        $this->uploadDataToS3($events,'events',$storeInTmp, $prefix);
    }

    /**
     * @param $customers
     * @param bool $storeInTmp
     * @throws ZaiusException
     */
    public function uploadCustomers($customers,$storeInTmp = false, $prefix = null) {
        $this->uploadDataToS3($customers,'customers',$storeInTmp, $prefix);
    }

    /**
     * @param $products
     * @param bool $storeInTmp
     * @throws ZaiusException
     */
    public function uploadProducts($products,$storeInTmp = false, $prefix = null) {
        $this->uploadDataToS3($products,'products',$storeInTmp, $prefix);
    }

    /**
     * @param $orders
     * @param bool $storeInTmp
     * @throws ZaiusException
     */
    public function uploadOrders($orders,$storeInTmp = false, $prefix = null) {
        $this->uploadDataToS3($orders,'orders',$storeInTmp, $prefix);
    }

    /**
     * @param array $data
     * @param string $type
     * @throws ZaiusException
     */
    protected function validate($data,$type) {
        foreach($data as $datum) {
            switch ($type) {
                case 'events':
                    $requiredFields = ['type'=>'','action' => '','identifiers' => 'array','data' => 'array'];
                    break;
                case 'customers':
                    $requiredFields = ['customer_id'=>''];
                    break;
                case 'orders':
                    $requiredFields = ['order'=>'array','identifiers'=>'array'];
                    break;
                case 'products':
                    $requiredFields = ['product_id'=>''];
                    break;
                default:
                    throw new ZaiusException("Unknown type");
            }
        }

        foreach($data as $datum) {
            foreach($requiredFields as $key=>$value) {
                if(!isset($datum[$key])) {
                    throw new ZaiusException("Key $key must be defined");
                }
                if($value == 'array' && !is_array($datum[$key])) {;
                    throw new ZaiusException("Key $key must contain an array");
                }
            }
        }


        return;
    }

    /**
     * @param array $data
     * @param string $type
     * @param bool $storeInTmp
     * @return \Aws\Result
     * @throws ZaiusException
     */
    protected function uploadDataToS3($data,$type,$storeInTmp = false, $prefix = null) {

        $this->validate($data,$type);

        $s3Translator = new S3Translator();
        switch($type) {
            case 'events':
                $translatedData = $s3Translator->translateEvents($data);
                $s3Type = 'event';
                $s3Extension = $prefix . 'events.zaius';
                break;
            case 'customers':
                $translatedData = $s3Translator->translateCustomers($data);
                $s3Type = 'customer';
                $s3Extension = $prefix . 'customers.zaius';
                break;
            case 'orders':
                $translatedData = $s3Translator->translateOrders($data);
                $s3Type = 'order';
                $s3Extension = $prefix . 'orders.zaius';
                break;
            case 'products':
                $translatedData = $s3Translator->translateProducts($data);
                $s3Type = 'product';
                $s3Extension = $prefix . 'products.zaius';
                break;
            default:
                throw new ZaiusException("Invalid S3 type");
        }


        $s3 = new \Aws\S3\S3Client([
            'version'=>'latest',
            'region'=>'us-east-1',
            'credentials' => [
                'key' => $this->keyId,
                'secret' => $this->secretAccessKey
            ]
        ]);

        $jsonBody = '';
        foreach($translatedData as $datum) {
            $tmp = ['type'=>$s3Type,'data'=>$datum];
            $jsonBody.=json_encode($tmp)."\n";
        }

        if($storeInTmp) {
            $bucket = self::ZAIUS_INCOMING_TMP;
        }
        else {
            $bucket = self::ZAIUS_INCOMING;
        }

        $ret = $s3->putObject([
            'Bucket' => $bucket,
            'Key' =>  $key = $this->trackerId.'/'.date('Y-m-d-H-i-s').'.'.$s3Extension,
            'Body' => $jsonBody
        ]);
        return $ret;
    }



}