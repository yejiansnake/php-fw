<?php
/**
 * 缓存管理器
 * User: yejian
 * Date: 2016/4/12
 * Time: 14:39
 */

namespace app\lib\common;

class ProgressHelper
{
    const KEY_PREFIX = 'PROG.';

    const PROGRESS_CODE_SUCCESS = 0;
    const PROGRESS_CODE_FINISH = 1;
    const PROGRESS_CODE_FAILED = 2;

    protected static $PROGRESS_INFO = [
        'code' => self::PROGRESS_CODE_SUCCESS,
        'msg' => '',
        'step' => 0,
        'step_desc' => '',
        'step_count' => 0,
        'finish' => 0,
        'total_count' => 0,
    ];

    public static function getProgressInfo()
    {
        return self::$PROGRESS_INFO;
    }

    public static function get($key)
    {
        $value = CachedHelper::get(self::KEY_PREFIX . $key);

        if (empty($value))
        {
            return null;
        }

        return $value;
    }

    public static function create(array $params = [])
    {
        $key = Guid::ToStringWeb();

        $progress = array_merge(self::$PROGRESS_INFO, $params);

        if (!self::set($key, $progress))
        {
            return null;
        }

        return $key;
    }

    public static function set($key, array $params = [])
    {
        if ($params == null)
        {
            $params = [];
        }

        $progress = array_merge(self::$PROGRESS_INFO, $params);

        $realKey = APP_SYS_NAME . self::KEY_PREFIX . $key;

        if (!CachedHelper::set($realKey, $progress, 600))
        {
            return false;
        }

        return true;
    }
}