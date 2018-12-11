<?php

namespace app\lib\bl;

use app\lib\common\LogHelper;

abstract class LogMgr extends LogHelper
{
    const TYPE_SYS = 'sys';
    const TYPE_TASK = 'task';
    const TYPE_EVENT = 'event';
    const TYPE_OPEN = 'open';
    const TYPE_CLOUD = 'cloud';
    const TYPE_WECHAT = 'wechat';

    public static function sys($module, $level, $info)
    {
        @parent::addLog(self::TYPE_SYS, $module, $level, $info);
    }

    public static function task($module, $level, $info)
    {
        @parent::addLog(self::TYPE_TASK, $module, $level, $info);
    }

    public static function event($module, $level, $info)
    {
        @parent::addLog(self::TYPE_EVENT, $module, $level, $info);
    }

    public static function open($module, $level, $info)
    {
        @parent::addLog(self::TYPE_OPEN, $module, $level, $info);
    }

    public static function wechat($module, $level, $info)
    {
        @parent::addLog(self::TYPE_WECHAT, $module, $level, $info);
    }

    public static function cloud($module, $level, $info)
    {
        @LogHelper::addLog(self::TYPE_CLOUD, $module, $level, $info);
    }
}