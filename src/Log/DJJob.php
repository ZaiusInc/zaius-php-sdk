<?php

namespace ZaiusSDK\Log;

use DJException;
use DJJob as CoreDJJob;
use ZaiusSDK\ZaiusClient;

/**
 * Class DJJob
 *
 * @package ZaiusSDK\Log
 */
class DJJob
{

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
    ) {
        $this->zaiusClient = $zaiusClient;
    }

    /**
     * Complete JSON error summary
     *
     * @return false|string
     */
    public function getErrorsSummaryJson()
    {
        try {
            $json = [
                'errorCount' => $this->countAllErrors(),
                'errors24h' => $this->countErrors24h(),
                'errors1h' => $this->countErrors1h(),
                'mostRecentErrorTs' => $this->getMostRecentErrorTs(),
            ];
        } catch (\Exception $exception) {
            $json = ['error' => $exception->getMessage()];
        }

        return json_encode($json);
    }

    /**
     * Count all errors in the database
     *
     * @return string
     * @throws DJException
     */
    public function countAllErrors()
    {
        $sql = sprintf(
            "SELECT COUNT(*) FROM `%s` WHERE failed_at is not null;",
            $this->zaiusClient->getJobTable()
        );
        $result = $this->runDDJobQuery($sql);
        return $result[0]['COUNT(*)'];
    }

    /**
     * Count all errors in the last 24h
     *
     * @return mixed
     * @throws DJException
     */
    public function countErrors24h()
    {
        $sql = sprintf("SELECT COUNT(*)
            FROM `%s` job
            WHERE job.failed_at >= DATE_SUB(NOW(), INTERVAL 1 DAY )
            AND job.failed_at is not null ", $this->zaiusClient->getJobTable());
        $result = $this->runDDJobQuery($sql);
        return $result[0]['COUNT(*)'];
    }

    /**
     * Count all errors in the last 1h
     *
     * @return mixed
     * @throws DJException
     */
    public function countErrors1h()
    {
        $sql = sprintf("SELECT COUNT(*)
            FROM `%s` job
            WHERE job.failed_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            AND job.failed_at is not null ", $this->zaiusClient->getJobTable());
        $result = $this->runDDJobQuery($sql);
        return $result[0]['COUNT(*)'];
    }

    /**
     * Get the most recent error timestamp
     *
     * @return array|false|int|null
     * @throws DJException
     */
    public function getMostRecentErrorTs()
    {
        $sql = sprintf("SELECT * FROM `%s`
            WHERE failed_at IS NOT NULL
            ORDER BY ID DESC LIMIT 1", $this->zaiusClient->getJobTable());
        $result = $this->runDDJobQuery($sql);
        $result = count($result) > 0 ? strtotime($result[0]['failed_at']) : null;

        return $result;
    }

    /**
     * Remove all errors in the Zaius job table
     *
     * @return array
     * @throws DJException
     */
    public function removeErrors()
    {
        $sql = sprintf(
            "DELETE FROM `%s` WHERE failed_at IS NOT NULL;",
            $this->zaiusClient->getJobTable()
        );
        $result = $this->runDDJobQuery($sql);
        return $result;
    }

    /**
     * Run a SQLQuery via DJJob
     *
     * @param $sqlQuery
     *
     * @return array
     * @throws DJException
     */
    private function runDDJobQuery($sqlQuery)
    {
        return CoreDJJob::runQuery($sqlQuery);
    }
}
