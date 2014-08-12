<?php
use HMinng\Log\Base\BaseLog;

class AtsLog extends BaseLog
{
    private static $instance = null;
    
    final private function __construct(){}
    
    final private function __clone(){}
    
    public static function getInstance()
    {
    	if (is_null(self::$instance)) {
    		self::$instance = new self();
    	}
    	
    	return self::$instance;
    }
}