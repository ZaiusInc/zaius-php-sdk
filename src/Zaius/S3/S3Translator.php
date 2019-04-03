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
        return $data;
    }

    public function translateOrders($data)
    {
        $translatedData = array();

        foreach ($data as $datum) {
            $translatedDataItem = array();
            foreach ($datum['identifiers'] as $key => $value) {
                $translatedDataItem[$key] = $value;
            }
            $translatedDataItem['order'] = $datum['order'];

            $translatedData[] = $translatedDataItem;
        }

        return $translatedData;
    }

    public function translateProducts($data)
    {
        return $data;
    }
}
