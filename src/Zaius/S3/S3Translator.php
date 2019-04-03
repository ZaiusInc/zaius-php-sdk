<?php

namespace ZaiusSDK\Zaius\S3;

class S3Translator
{
    public function translateEvents($data)
    {
        $translatedData = array();
        foreach ($data as $datum) {
            $translatedDataItem = array();
            $translatedDataItem['type'] = $datum['type'];
            $translatedDataItem['data']['action'] = $datum['action'];
            if (isset($datum['identifiers'])) {
                foreach ($datum['identifiers'] as $key => $identifier) {
                    $translatedDataItem['data'][$key] = $identifier;
                }
            }
            if (isset($datum['data'])) {
                foreach ($datum['data'] as $key => $value) {
                    $translatedDataItem['data'][$key] = $value;
                }
            }

            $translatedData[] = $translatedDataItem;
        }

        return $translatedData;
    }

    public function translateCustomers($data)
    {
        $translatedData = array();
        foreach ($data as $datum) {
            $translatedDataItem = array();
            $translatedDataItem['type'] = "customer";
            if (isset($datum['attributes'])) {
                foreach ($datum['attributes'] as $key => $value) {
                    $translatedDataItem['data'][$key] = $value;
                }
            }
            $translatedData[] = $translatedDataItem;
        }
        return $translatedData;
    }

    public function translateOrders($data)
    {
        // Orders are a special case of events.
        return $this->translateEvents($data);
    }

    public function translateProducts($data)
    {
        $translatedData = array();
        foreach ($data as $datum) {
            $translatedDataItem = array();
            $translatedDataItem['type'] = "product";
            $translatedDataItem['data'] = $datum;
            $translatedData[] = $translatedDataItem;
        }
        return $translatedData;
    }
}
