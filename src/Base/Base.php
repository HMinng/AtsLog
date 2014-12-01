<?php
namespace HMinng\Log\Base;

use HMinng\Log\SHM\SHMLibrary;

class Base
{
    public static $startTime = NUll;

    public static $params = array();

    protected static $level = NULL;

    private static $time = NULL;

    private static $date = NULL;

    private static $fields = array();

    private static $configures = NULL;

    private static $projectConfigures = NULL;

    private static $isWrite = true;

    private static $suffix = '.log';

    private static $tmpLogFile = 'tmp_log';

    private static function init($message)
    {
        self::$time = time();

        self::$params['output'] = $message;

        BaseConfig::init();

        if (is_null(self::$projectConfigures)) {
            self::$projectConfigures = BaseConfig::getProjectConfigures();
        }

        if (is_null(self::$configures)) {
            self::$configures = BaseConfig::getBaseConfigures();
        }

        self::traces();

        self::isWrite();

        self::$fields = self::getFields();

        return true;
    }

    private static function getFields()
    {
        $fieldsConfigures = BaseConfig::getFieldsConfigures();
        $businessConfigures = BaseConfig::getBusinessConfigures();

        /** @var $fields array */
        $fields = array();
        foreach ($businessConfigures as $key => $value) {
            if ( ! in_array($key, $fieldsConfigures)) {
              continue;
            }

            if (( ! self::$isWrite && in_array($key, self::$projectConfigures['write_level']['no_field']))) {
                continue;
            }

            eval($value);
        }

        return $fields;
    }

    private static function traces()
    {
        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, self::$projectConfigures['traces']['number']);

        $line = self::$projectConfigures['traces']['line'];
        if ($traces[$line + 1]['function'] == '__construct') {
            $line += 1;
        }

        self::$params['line'] = $traces[$line]['line'];
        self::$params['file'] = $traces[$line]['file'];
        self::$params['traces'] = json_encode($traces);

        return true;
    }

    private static function isWrite()
    {
        self::$isWrite = true;

        $writeLevle = array_key_exists(self::$configures['write_level'], self::$projectConfigures['write_level']['write']) ? self::$projectConfigures['write_level']['write'][self::$configures['write_level']] : 0;
        $currentLevle = array_key_exists(self::$level, self::$projectConfigures['write_level']['write']) ? self::$projectConfigures['write_level']['write'][self::$level] : 0;

        if ($currentLevle < $writeLevle) {
            self::$isWrite = false;
        }

        return true;
    }

    private static function getFileName()
    {
        if ( ! is_dir(self::$configures['path'])) {
            @mkdir(self::$configures['path'], 0777);
        }

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

        $tempFileName = $fileName . '-' . $num;
        $isFile = $tempFileName . self::$suffix;

        if (is_file($isFile)) {
            $fileSize = filesize($isFile);

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
                $tempFileName = $fileName . '-' . ++ $num;
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

        $string = implode(self::$fields, "\t");

        if (self::$configures['is_compression'] == 1) {
            $string = gzdeflate($string, 9);
        }

        switch(self::$configures['writing_position']) {
            case 'local':
                return self::localFileSystem($string);break;
            case 'remote':
                return self::remoteFileSystem($string);break;
            case 'queue':
                return self::localQueueSystem($string);break;
            default:
                return self::localFileSystem($string);

        }
    }

    private static function localFileSystem($string)
    {
        $fileName = self::getFileName();

        $fileName = self::processFileSize($fileName);

        return error_log($string . "\r\n\r\n\r\n", 3, $fileName . self::$suffix);
    }

    private static function remoteFileSystem($string)
    {
        $level = array('error' => LOG_ERR, 'waring' => LOG_WARNING, 'info' => LOG_INFO, 'debug' => LOG_DEBUG);

        openlog('PHP_LOG', LOG_PID, self::$configures['syslog_local']);
        syslog($level[self::$level], $string);
        return closelog();
    }

    private static function localQueueSystem($string)
    {
        $numbers = SHMLibrary::getQueueMessageNumbers();

        if (self::$configures['write_number'] == 0) {
            if (self::$configures['queue_persistence_position'] == 1) {
                return self::localFileSystem($string);
            } else {
                return self::remoteFileSystem($string);
            }
        } else if ($numbers >= self::$configures['write_number']) {
            return self::writeTmpFile($string);
        }

        $flag = SHMLibrary::addMessageToQueue($string);

        if ( ! $flag) {
            self::writeTmpFile($string);
        }

        return true;
    }

    private static function writeTmpFile($string)
    {
        $file = self::$configures['path'] . DIRECTORY_SEPARATOR . self::$tmpLogFile . self::$suffix;

        error_log($string . "\r\n\r\n\r\n", 3, $file);

        return true;
    }

    protected static function uppack($string)
    {
        return gzinflate($string);
    }
}