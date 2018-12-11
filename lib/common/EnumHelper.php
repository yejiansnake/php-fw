<?php
/**
 * Created by PhpStorm.
 * User: crazySnake
 * Date: 2018/11/1
 * Time: 17:11
 */

namespace app\lib\common;


abstract class EnumHelper
{
    public static function getData(array $map, $type)
    {
        $obj = array_key_exists($type, $map) ? $map[$type] : NULL;
        return $obj;
    }

    public static function getValue(array $map, $type, $filedName)
    {
        $obj = array_key_exists($type, $map) ? $map[$type] : NULL;

        $res = '';
        if (!empty($obj) && !empty($obj[$filedName]))
        {
            $res = $obj[$filedName];
        }

        return $res;
    }
}