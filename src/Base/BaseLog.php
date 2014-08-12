<?php
namespace HMinng\Log\Base;

use Symfony\Component\Yaml\Yaml;

abstract class BaseLog
{
    public $startTime;
    
    public $input = array();
    
    private $fields = array();
    
    private $level = null;
    
    private $params = array();
    
    public function __call($method, $params)
    {
    	$this->level = $method;
    	
    	$this->traces($params[0]);
    	
    	$this->id($params[0]);
    	
    	$this->other($params[0]);
    	
    	$this->fields();
    	
    	$this->write();
    	
    	return true;
    }
    
    private function traces($params)
    {
    	if (array_key_exists('traces', $params)) {
    		$this->params['line'] = $params['line'];
        	$this->params['file'] = $params['file'];
        	$this->params['traces'] = json_encode($params['traces']);
    	} else {
        	$traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 20);
        	
        	$this->params['line'] = $traces[1]['line'];
        	$this->params['file'] = $traces[1]['file'];
        	$this->params['traces'] = json_encode($traces);
    	}
    	
    	return true;
    }
    
    private function id($params)
    {
        if ( ! array_key_exists('trace_id', $params)) {
        	throw new \Exception('trace id not found!');
        }
        
        $traceId = $params['trace_id'];
    	$systemId = \Yaf_Registry::get('config')->application->system->id;
    	$sourceId = array_key_exists('source_id', $params) ? $params['source_id'] : 1;
    	$customId = array_key_exists('custom_id', $params) ? $params['custom_id'] : 0;
    	
    	$this->params['id'] = '[' . $traceId . '_' . $systemId . '_' . $sourceId . '_' . $customId . ']';
    	
    	return true;
    }
    
    private function other($params)
    {
    	$this->params['info'] = array_key_exists('info', $params) ? $params['info'] : null;
    	$this->params['output'] = array_key_exists('output', $params) ? $params['output'] : null;
    	
    	return true;
    }
    
    private function getUserConf()
    {
        $file = APPLICATION_PATH . '/../application/library/config/Log.yml';
        
        if ( ! is_file($file)) {
        	throw new \Exception('Log.yml file not found!');
        }
        
        $conf = Yaml::parse(APPLICATION_PATH . '/../application/library/config/Log.yml');
        
        return $conf['log'];
    }
    
    private function getSystemConf()
    {
    	 $conf = Yaml::parse(__DIR__ . '/../Conf/Log.yml');
    	 
    	 return $conf['log'];
    }
    
    private function fields()
    {
    	$userConf = $this->getUserConf();
    	$systemConf = $this->getSystemConf();
    	
    	foreach ($systemConf as $key => $value) {
    		if (in_array($key, $userConf)) {
    	       eval($value);
    		}
    	}
    	
    	return true;
    }
    
    private function write()
    {
    	error_log(implode("\t", $this->fields) . "\r\n\r\n\r\n", 3, APPLICATION_PATH . '/../application/log/' . date('Y-m-d') . '.log');
    }
}