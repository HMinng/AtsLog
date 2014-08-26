<?php
use HMinng\Log\Base\Base;
use HMinng\Log\Product\Product;

class AtsLog extends Base
{
    /**
     * @param string $message 错误信息
     * @param int $traceId 链路ID
     * @param int $sourceId 错误号
     * @param int $customId 自定义ID
     * @param array $params 包括input，info信息
     * @internal param null $int $customId 自定义ID
     */
    public static function error($message, $traceId, $sourceId = 0, $customId = 0, $params = array())
    {
        self::$params['id'] = Product::id($traceId, $sourceId, $customId);

        self::process('error', $message, $params);
    }

    /**
     * @param string $message 警告信息
     * @param int $traceId 链路ID
     * @param int $sourceId 错误号
     * @param int $customId 自定义ID
     * @param array $params 包括input，info信息
     */
    public static function waring($message, $traceId, $sourceId = 0, $customId = 0, $params = array())
    {
        self::$params['id'] = Product::id($traceId, $sourceId, $customId);

        self::process('waring', $message, $params);
    }

    /**
     * @param string $message 打印信息
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

    /**
     * @param string $message 通知信息
     * @param array $params 包括input，info信息
     */
    public static function notice($message, $params = array())
    {
        self::process('notice', $message, $params);
    }

    private static function setParams($params)
    {
        if (array_key_exists('input', $params) && ! empty($params['input'])) {
            self::$params['input'] = $params['input'];
        }

        if (array_key_exists('info', $params) && ! empty($params['info'])) {
            self::$params['info'] = $params['info'];
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