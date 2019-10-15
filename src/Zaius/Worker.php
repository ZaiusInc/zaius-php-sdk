<?php

namespace ZaiusSDK\Zaius;

use ZaiusSDK\ZaiusException;

/**
 * Class Worker
 * @package ZaiusSDK\Zaius
 */
class Worker extends \DJWorker
{
    /**
     * Worker constructor.
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if (!isset($options['sleep'])) {
            $options['sleep'] = 0;
        }
        if (!isset($options["max_attempts"])) {
            $options['max_attempts'] = 1;
        }

        parent::__construct($options);
    }

    public function processAll()
    {
        while ($job = self::getNewJob('default')) {
            $job->run();
        }
    }
}
