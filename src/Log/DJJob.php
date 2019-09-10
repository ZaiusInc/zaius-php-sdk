<?php

namespace ZaiusSDK\Log;

use DJException;
use ZaiusSDK\ZaiusClient;

class DJJob{

    /**
     * @var ZaiusClient
     */
    private $zaiusClient;

    /**
     * Errors constructor.
     *
     * @param ZaiusClient $zaiusClient
     */
    public function __construct(
        ZaiusClient $zaiusClient
    ){
        $this->zaiusClient = $zaiusClient;
    }

    /**
     * @param $sqlQuery
     *
     * @return array
     * @throws DJException
     */
    private function runDDJobQuery($sqlQuery)
    {
        return \DJJob::runQuery($sqlQuery);
    }

    /**
     * Count all errors in the database
     *
     * @return string
     * @throws DJException
     */
    public function countAllErrors(){

        $sql = sprintf(
            "SELECT COUNT(*) FROM `%s` WHERE failed_at is not null;",
            $this->zaiusClient->getJobTable()
        );
        $result = $this->runDDJobQuery($sql);
        return $result[0]['COUNT(*)'];
    }

    public function countErrors24h(){

        $sql = sprintf("SELECT COUNT(*)
            FROM `%s` job
            WHERE job.failed_at >= DATE_SUB(NOW(), INTERVAL 1 DAY )
            AND job.failed_at is not null ", $this->zaiusClient->getJobTable());
        $result = $this->runDDJobQuery($sql);
        return $result[0]['COUNT(*)'];
    }

    public function countErrors1h(){

        $sql = sprintf("SELECT COUNT(*)
            FROM `%s` job
            WHERE job.failed_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND job.failed_at is not null ", $this->zaiusClient->getJobTable());
        $result = $this->runDDJobQuery($sql);
        return $result[0]['COUNT(*)'];
    }

    public function getMostRecentErrorTs(){

        $sql = sprintf("SELECT * FROM `%s`
            WHERE failed_at is not null
            ORDER BY ID DESC LIMIT 1", $this->zaiusClient->getJobTable());
        $result = $this->runDDJobQuery($sql);
        $result = count($result) > 0 ? strtotime($result[0]['failed_at']) : null;

        return $result;
    }

    /**
     * @return array
     * @throws DJException
     */
    public function removeErrors(){
        $sql = sprintf("DELETE FROM `%s` WHERE failed_at IS NOT NULL;",
            $this->zaiusClient->getJobTable());
        $result = $this->runDDJobQuery($sql);
        return $result;
    }
}