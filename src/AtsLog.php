<?php
use HMinng\Log\Base\Base;
use HMinng\Log\Product\Product;

class AtsLog extends Base
{
    /**
     * @param string $message 错误信息
     * @param array $params 包括input，info信息
     */
    public static function error($message, $params = array())
    {
        self::$params['id'] = $params['id'];

        self::process('error', $message, $params);
    }

    /**
     * @param string $message 警告信息
     * @param array $params 包括input，info信息
     */
    public static function waring($message, $params = array())
    {
        self::$params['id'] = $params['id'];

        self::process('waring', $message, $params);
    }

    /**
     * @param string $message 打印信息
     * @param int  $traceId   链路id
     * @param array $params 包括input，info信息
     */
    public static function info($message, $params = array())
    {
        self::$params['id'] = $params['id'];

        self::process('info', $message, $params);
    }

    /**
     * @param string $message debug信息
     * @param array $params 包括input，info信息
     */
    public static function debug($message, $params = array())
    {
        self::$params['id'] = $params['id'];

        self::process('debug', $message, $params);
    }

    private static function setParams($params)
    {
        if (array_key_exists('input', $params) && ! empty($params['input'])) {
            self::$params['input'] = json_encode($params['input']);
        }

        if (array_key_exists('info', $params) && ! empty($params['info'])) {
            self::$params['info'] = json_encode($params['info']);
        }

        if ( ! array_key_exists('id', self::$params)) {
            self::$params['id'] = NULL;
        }

        return true;
    }

    private static function process($level, $message, $params)
    {
        self::$level = $level;

        self::setParams($params);

        self::write($message);
    }
}