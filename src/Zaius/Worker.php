<?php

namespace ZaiusSDK\Zaius;

class Worker extends \DJWorker {
    public function __construct(array $options = array())
    {
        if(!isset($options['sleep'])) {
            $options['sleep'] = 0;
        }
        parent::__construct($options);
    }

    public function processAll() {
        while($job = self::getNewJob('default')) {
            $job->run();
        }
    }

}