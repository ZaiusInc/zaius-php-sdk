<?php

namespace ZaiusSDK\Zaius;

use ZaiusSDK\ZaiusException;

class Worker extends \DJWorker
{
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
