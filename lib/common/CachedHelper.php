<?php

namespace app\lib\common;

abstract class CachedHelper
{
    private static function cache()
    {
        return \Yii::$app->cache;
    }

    public static function get($key, $delete = false)
    {
        $obj = self::cache();

        $res = $obj->get($key);

        if (empty($res))
        {
            return null;
        }

        if ($delete)
        {
            $obj->delete($key);
        }

        return $res;
    }

    //过期时间为当前时间增加的时间值（单位为秒）
    //$expireTime 小于 60×60×24×30 （30天时间的秒数）则是当前时间加上相应的秒数
    //$expireTime 超过 60×60×24×30（30天时间的秒数）则认为是一个标准的 UNIX 时间
    public static function set($key, $value, $expireTime = null)
    {
        $obj = self::cache();

        if (!$obj->set(
            $key,
            $value,
            $expireTime))
        {
            return false;
        }

        return true;
    }

    public static function getOrSet($key, $callable, $expireTime = null)
    {
        $value = self::get($key);

        if (!empty($callable) && is_callable($callable))
        {
            $value = call_user_func($callable);

            if (!is_null($value))
            {
                self::set($key, $value, $expireTime);
            }
        }

        return $value;
    }

    public static function delete($key)
    {
        $obj = self::cache();
        $obj->delete($key);
    }
}