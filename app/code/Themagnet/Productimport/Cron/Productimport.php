<?php

namespace Themagnet\Productimport\Cron;

class Productimport 
{
    protected $_importproduct;
    protected $logger;
    protected $_importlogger;
	public function __construct(
        \Themagnet\Productimport\Model\Importproduct $importproduct,
        \Themagnet\Productimport\Model\Logger $importlogger
    ) {
        $this->_importproduct = $importproduct;
        $this->_importlogger = $importlogger;
    }

    public function execute() {
    	
    	try{
            $this->_importlogger->debugLog((string)__('Cron start'));
	        $this->_importproduct->createSimpleFile();
            $this->_importproduct->createConfigFile();
            $this->_importlogger->debugLog((string)__('Cron end'));
	    }
	    catch(\Exception $e){
            $this->logger->info($e->getMessage());
            $this->logger->debug($e->getMessage());
	    }
    }
}