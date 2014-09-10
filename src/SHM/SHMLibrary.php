<?php
namespace HMinng\Log\SHM;

use HMinng\SHMLibrary\Queue\Queue;
use HMinng\SHMLibrary\Memory\Memory;

class SHMLibrary
{
    const QUEUE_NUMBER = 100;
    const CONFIGURES_NUMBER = 1;
    const SAVE_TIME_NUMBER = 2;
    const SAVE_PERIOD = 3600;

    public static function addMessageToQueue($string)
    {
        Queue::open();
        $status = Queue::stat();

        Memory::open();

        $memSize = Memory::read(self::QUEUE_NUMBER);
        if ($memSize === false) {
            $memSize = 0;
        }

        $size = Memory::getMemorySize($string) + $memSize;

        if ($size > $status['msg_qbytes']) {
            return false;
        }

        Memory::update(self::QUEUE_NUMBER, $size);

        Queue::add($string);

        return true;
    }

    public static function getQueueMessageNumbers()
    {
        Queue::open();

        $status = Queue::stat();
        return $status['msg_qnum'];
    }

    public static function getConfigures()
    {
        Memory::open();

        $configureFile = Memory::read(self::CONFIGURES_NUMBER);
        $foretime = Memory::read(self::SAVE_TIME_NUMBER);

        if (is_numeric($foretime) && (time() - $foretime) >= self::SAVE_PERIOD) {
            return false;
        }

        return $configureFile;
    }

    public static function addConfiguresToMemory($configureFile)
    {
        Memory::open();

        Memory::add(self::SAVE_TIME_NUMBER, time());
        Memory::add(self::CONFIGURES_NUMBER, $configureFile);

        return true;
    }
}