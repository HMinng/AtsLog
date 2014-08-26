<?php
namespace HMinng\Log\Base;

use HMinng\Log\Conf\BaseConfig;

class Base
{
    const INVOKE_LINE = 4;

    const NUMBER = 20;

    public static $startTime = NUll;

    public static $params = array();

    protected static $level = NULL;

    private static $time = NULL;

    private static $date = NULL;

    private static $fields = array();

    private static $configures = NULL;

    private static function init($message)
    {
        self::$time = time();

        self::$params['output'] = json_encode($message);

        BaseConfig::init();

        self::traces();

        self::$fields = self::getFields();

        if (is_null(self::$configures)) {
            self::$configures = BaseConfig::getBaseConfigures();
        }

        return true;
    }

    private static function getFields()
    {
        $fieldsConfigures = BaseConfig::getFieldsConfigures();
        $businessConfigures = BaseConfig::getBusinessConfigures();

        /** @var $fields array */
        $fields = array();
        foreach ($businessConfigures as $key => $value) {echo 1 . '<br/>';
            if (in_array($key, $fieldsConfigures)) {
                eval($value);
            }
        }

        return $fields;
    }

    private static function traces()
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, self::NUMBER);

        $number = self::INVOKE_LINE;
        if ($traces[$number + 1]['function'] == '__construct') {
            $number += 1;
        }

        self::$params['line'] = $traces[$number]['line'];
        self::$params['file'] = $traces[$number]['file'];
        self::$params['traces'] = json_encode($traces);

        return true;
    }

    private static function getFileName()
    {
        $fileName = self::$configures['path'] . DIRECTORY_SEPARATOR;

        if (self::$configures['split_by_level'] == 1) {
            $fileName .= strtoupper(self::$level) . '.';
        }

        if (self::$configures['split_by_hore'] == 1) {
            self::$date = date('YmdH', self::$time);
        } else {
            self::$date = date('Ymd', self::$time);
        }

        return $fileName . self::$date;
    }

    private static function processFileSize($fileName)
    {
        if (self::$configures['split_by_size'] == 0) {
            return $fileName;
        }

        $file = self::$configures['path'] . DIRECTORY_SEPARATOR . '.num';

        $num = 1;
        if ( ! is_file($file)) {
            @touch($file);
        }

        $source = fopen($file, 'a+');

        if ( ! flock($source, LOCK_EX)) {
            throw new \Exception('file lock open failure!');
        }

        $data = fread($source, 4096);

        if ( ! empty($data)) {
            ftruncate($source, 0);

            $data = explode("\t", $data);
            if (self::$date <= $data[0]) {
                $num = $data[1];
            }
        }

        $tempFileName = $fileName . '-' . $num . '.log';

        if (is_file($tempFileName)) {
            $fileSize = filesize($tempFileName);

            $size = self::$configures['split_by_size'];

            $len = strlen($size);

            $unit = strtoupper($size{$len-1});

            switch ($unit) {
                case 'M':
                    $fileSize = ceil($fileSize / 1024 / 1024);
                    break;
                case 'G':
                    $fileSize = ceil($fileSize / 1024 / 1024 / 1024);
                    break;
                default:
                    throw new \Exception('file size type error, log write failure!');
            }

            $size = substr($size, 0, $unit - 1);

            if ($fileSize > $size) {
                $tempFileName = $fileName . '-' . ++ $num  . '.log';
            }
        }

        fwrite($source, self::$date . "\t" . $num);

        flock($source, LOCK_UN);

        fclose($source);

        return $tempFileName;
    }

    protected static function write($message)
    {
        self::init($message);

        $fileName = self::getFileName();

        $fileName = self::processFileSize($fileName);

        error_log(implode(self::$fields, "\t") . "\r\n\r\n\r\n", 3, $fileName);
    }
}