<?php
namespace HMinng\Log\Product;

class Product
{
    public static function id($traceId, $sourceId = 0, $customId = 0)
    {
        $systemId = \Yaf_Registry::get('config')->application->system->id;

        return '[' . $traceId . '_' . $systemId . '_' . $sourceId . '_' . $customId . ']';
    }
}