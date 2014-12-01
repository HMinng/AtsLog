<?php
namespace HMinng\Log\Base;

use Symfony\Component\Yaml\Yaml;
use HMinng\Log\SHM\SHMLibrary;

class BaseConfig
{
    private static $businessConfigures = NULL;

    private static $fieldsConfigures = NULL;

    private static $baseConfigures = NULL;

    private static $projectConfigures = NULL;

    private static $remoteConfigureServer = NULL;

    public static function init()
    {
        self::setConfigures();
        self::setBusinessConfigures();
        self::setProjectConfigures();

        self::checkPath();

        return true;
    }

    private static function setConfigures()
    {
        if ( ! is_null(self::$fieldsConfigures) && ! is_null(self::$baseConfigures)) {
            return true;
        }

        if (is_null(self::$remoteConfigureServer)) {
            $configureFile = __DIR__ . '/../../../../conf/custom/Log.yml';

            if ( ! is_file($configureFile)) {
                $configureFile = __DIR__ . '/../Conf/Base/Base.yml';
            }
            $configureFile = file_get_contents($configureFile);
        } else {
            $configureFile = SHMLibrary::getConfigures();

            if ( ! $configureFile) {
                $configureFile = file_get_contents(self::$remoteConfigureServer);
                SHMLibrary::addConfiguresToMemory($configureFile);
            }
        }

        $configures = self::parse($configureFile);

        self::$baseConfigures = $configures['conf'];
        self::$fieldsConfigures = $configures['product'];

        return true;
    }

    private static function setBusinessConfigures()
    {
        if ( ! is_null(self::$businessConfigures)) {
            return true;
        }

        $configures = self::parse(file_get_contents(__DIR__ . '/../Conf/Business/Business.yml'));

        $configures = array_merge($configures['base'], $configures['product']);

        self::$businessConfigures = $configures;

        return true;
    }

    private static function setProjectConfigures()
    {
        if ( ! is_null(self::$projectConfigures)) {
            return true;
        }

        self::$projectConfigures = self::parse(file_get_contents(__DIR__ . '/../Conf/Project/Project.yml'));

        return true;
    }

    private static function checkPath()
    {
        if (empty(self::$baseConfigures['path'])) {
            throw new \Exception('Please configure the path parameter!');
        }

        if ( ! is_dir(self::$baseConfigures['path'])) {
            throw new \Exception('The path parameter must be a directory!');
        }

        if ( ! is_writable(self::$baseConfigures['path'])) {
            throw new \Exception('The path directory must be writable!');
        }

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

    public static function getProjectConfigures()
    {
        return self::$projectConfigures;
    }

    public static function parse($content) {
        return Yaml::parse($content);
    }
}