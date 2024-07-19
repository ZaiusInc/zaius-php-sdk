<?php

namespace ZaiusSDK\Zaius;

class Worker /* extends \DJWorker */ {
    public function __construct(array $options = array())
    {
        // ZAI-224 DEPRECATED UNTIL RE-IMPLEMENTATION WITH NEW LIBRARY: 
        throw new \Exception("Queuing system has been disabled"); 
        return;
        // ZAI-224 END 
        
        if(!isset($options['sleep'])) {
            $options['sleep'] = 0;
        }
        parent::__construct($options);
    }

    public function processAll() {
        // ZAI-224 DEPRECATED UNTIL RE-IMPLEMENTATION WITH NEW LIBRARY:
        throw new \Exception("Queuing system has been disabled"); 
        return;
        // END ZAI-224
        
        while($job = self::getNewJob('default')) {
            $job->run();
        }
    }

}
