<?php
/**
 * Session 对象基类
 * User: yejian
 * Date: 2016/3/15
 * Time: 10:11
 */

namespace app\lib\common\session;

abstract class BaseSession
{
    //对应的Session key，需要重载的时候覆盖
    public static function getKey($keyParams = [])
    {
        return null;
    }

    public static function exist($keyParams = [])
    {
        $key = static::getKey($keyParams);

        if (empty($key))
        {
            return false;
        }

        if (!isset($_SESSION[$key]))
        {
            return false;
        }

        return true;
    }

    public static function get($keyParams = [])
    {
        $key = static::getKey($keyParams);

        if (empty($key))
        {
            return null;
        }

        if (!isset($_SESSION[$key]))
        {
            return null;
        }

        return $_SESSION[$key];
    }

    public static function set($value, $keyParams = [])
    {
        $key = static::getKey($keyParams);

        if (empty($key))
        {
            return false;
        }

        $_SESSION[$key] = $value;

        return true;
    }

    //清理(类似unset)
    public static function clear($keyParams = [])
    {
        $key = static::getKey($keyParams);

        if (empty($key))
        {
            return false;
        }

        unset($_SESSION[$key]);

        return true;
    }
}