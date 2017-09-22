<?php
use HMinng\Log\Base\Base;

class AtsLog extends Base
{
    public static $input = array();

    public static $traceID = NULL;

    //日志级别
    const EMERG     = 'emerg';  // 严重错误: 导致系统崩溃无法使用
    const WARING    = 'waring';  // 警戒性错误: 必须被立即修改的错误
    const ERROR     = 'error';  	// 一般错误: 一般性错误
    const DEBUG     = 'debug';  // debug信息
    const INFO		= 'info';   // 日志信息log
    const RECORD	= 'record';	// 一般性日志


    /**
     * @param string $message 错误信息
     * @param array $params 包括input，info信息
     */
    public static function emerg($message, $params = array(), $force = false)
    {
        self::process(self::EMERG, $message, $params, $force);
    }

    /**
     * @param string $message 错误信息
     * @param array $params 包括input，info信息
     */
    public static function error($message, $params = array(), $force = false)
    {
        self::process(self::ERROR, $message, $params, $force);
    }

    /**
     * @param string $message 警告信息
     * @param array $params 包括input，info信息
     */
    public static function waring($message, $params = array(), $force = false)
    {
        self::process(self::WARING, $message, $params, $force);
    }

    /**
     * @param string $message 打印信息
     * @param int  $traceId   链路id
     * @param array $params 包括input，info信息
     */
    public static function info($message, $params = array(), $force = false)
    {
        self::process(self::INFO, $message, $params, $force);
    }

    /**
     * @param string $message 一般日志信息
     * @param array $params 包括input，info信息
     */
    public static function record($message, $params = array(), $force = false)
    {
        self::process(self::RECORD, $message, $params, $force);
    }

    /**
     * @param string $message debug信息
     * @param array $params 包括input，info信息
     */
    public static function debug($message, $params = array(), $force = false)
    {
        self::process(self::DEBUG, $message, $params, $force);
    }

    private static function genTraceId()
    {
        $time = microtime(true);
        $time = explode('.', $time);

        $rand = rand(0,999);

        $time = $time[0] . $time[1] * 1000 . $rand;
        return $time;
    }

    private static function setParams($params, $force)
    {
        if ( ! empty(self::$input)) {
            self::$params['input'] = json_encode(self::$input);
        }

        if (array_key_exists('info', $params) && ! empty($params['info'])) {
            self::$params['info'] = json_encode($params['info']);
        }

        if (is_null(self::$traceID) || $force) {
            self::$traceID = self::genTraceId();
        }

        self::$params['id'] = self::$traceID;

        return true;
    }

    private static function process($level, $message, $params, $force)
    {
        self::$level = $level;

        self::setParams($params, $force);

        self::write($message);
    }
}