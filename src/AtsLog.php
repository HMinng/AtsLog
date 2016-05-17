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
    public static function error($message, $params = array())
    {
        self::process('error', $message, $params);
    }

    /**
     * @param string $message 警告信息
     * @param array $params 包括input，info信息
     */
    public static function waring($message, $params = array())
    {
        self::process('waring', $message, $params);
    }

    /**
     * @param string $message 打印信息
     * @param int  $traceId   链路id
     * @param array $params 包括input，info信息
     */
    public static function info($message, $params = array())
    {
        self::process('info', $message, $params);
    }

    /**
     * @param string $message debug信息
     * @param array $params 包括input，info信息
     */
    public static function debug($message, $params = array())
    {
        self::process('debug', $message, $params);
    }

    private static function setParams($params)
    {
        if ( ! empty(self::$input)) {
            self::$params['input'] = json_encode(self::$input);
        }

        if (array_key_exists('info', $params) && ! empty($params['info'])) {
            self::$params['info'] = json_encode($params['info']);
        }

        self::$params['id'] = self::$traceID;

        return true;
    }

    private static function process($level, $message, $params)
    {
        self::$level = $level;

        self::setParams($params);

        self::write($message);
    }
}