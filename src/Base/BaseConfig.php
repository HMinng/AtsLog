<?php
namespace HMinng\Log\Conf;

use Symfony\Component\Yaml\Yaml;

class BaseConfig
{
    private static $businessConfigures = NULL;

    private static $fieldsConfigures = NULL;

    private static $baseConfigures = NULL;

    public static function init()
    {
        self::setConfigures();
        self::setBusinessConfigures();

        return true;
    }

    private static function setConfigures()
    {
        if ( ! is_null(self::$fieldsConfigures) && ! is_null(self::$baseConfigures)) {
            return true;
        }

        $configureFile = __DIR__ . '/../../../../conf/custom/Log.yml';

        if ( ! is_file($configureFile)) {
            $configureFile = __DIR__ . '/../Conf/Base/Base.yml';
        }

        $configures = Yaml::parse($configureFile);

        self::$baseConfigures = $configures['conf'];
        self::$fieldsConfigures = $configures['product'];

        return true;
    }

    private static function setBusinessConfigures()
    {
        if ( ! is_null(self::$businessConfigures)) {
            return true;
        }

        $configures = Yaml::parse(__DIR__ . '/../Conf/Business/Business.yml');

        $configures = array_merge($configures['base'], $configures['product']);

        self::$businessConfigures = $configures;

        return true;
    }

    public static function getBaseConfigures()
    {
        return self::$baseConfigures;
    }

    public static function getFieldsConfigures()
    {
        return self::$fieldsConfigures;
    }

    public static function getBusinessConfigures()
    {
        return self::$businessConfigures;
    }
}