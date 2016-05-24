<?php
use HMinng\Log\Base\Base;

class AtsLog extends Base
{
    public static $input = array();

    public static $traceID = NULL;

    /**
     * @param string $message 错误信息
     * @param array $params 包括input，info信息
     */
    public static function error($message, $params = array(), $force = false)
    {
        self::process('error', $message, $params, $force);
    }

    /**
     * @param string $message 警告信息
     * @param array $params 包括input，info信息
     */
    public static function waring($message, $params = array(), $force = false)
    {
        self::process('waring', $message, $params, $force);
    }

    /**
     * @param string $message 打印信息
     * @param int  $traceId   链路id
     * @param array $params 包括input，info信息
     */
    public static function info($message, $params = array(), $force = false)
    {
        self::process('info', $message, $params, $force);
    }

    /**
     * @param string $message debug信息
     * @param array $params 包括input，info信息
     */
    public static function debug($message, $params = array(), $force = false)
    {
        self::process('debug', $message, $params, $force);
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