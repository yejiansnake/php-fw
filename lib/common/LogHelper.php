<?php

namespace app\lib\common;

abstract class LogHelper
{
    const TYPE_COMMON = 'common';

    const LEVEL_DEBUG = 4;
    const LEVEL_INFO = 3;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 1;
    const LEVEL_NONE = 0;

    private static $config = null;

    public static function common($module, $level, $info)
    {
        @self::addLog(self::TYPE_COMMON, $module, $level, $info);
    }

    public static function addLog($type, $module, $level, $info)
    {
        if (!self::isLog($type,$level))
        {
            return;
        }

        $dirPath = self::getLogDir($type);

        $curDate = DateTimeEx::getString(['format' => DateTimeEx::DATE_FORMAT]);
        $curDateTime = DateTimeEx::getString();

        $filePath = $dirPath ."/{$type}_{$curDate}.log";

        if (file_exists($filePath))
        {
            if (filesize($filePath) >= 20971520)
            {
                $tempTime = DateTimeEx::getNowString("Y-m-d_H-i-s");

                $newName = $dirPath ."/{$type}_{$tempTime}.log";
                rename($filePath, $newName);
            }
        }

        $content = "[{$curDateTime}] [{$module}] [{$level}] $info\r\n";

        file_put_contents($filePath, $content, FILE_APPEND);
    }

    private static function getConfig()
    {
        if (empty(self::$config))
        {
            if (!empty(\Yii::$app->params['logMgr']))
            {
                self::$config = \Yii::$app->params['logMgr'];
            }
        }

        return self::$config;
    }

    private static function isLog($type, $level)
    {
        $configLevel = self::getConfigLevel($type);

        if (!isset($configLevel))
        {
            $configLevel = YII_DEBUG ? self::LEVEL_DEBUG : self::LEVEL_INFO;
        }

        if ($configLevel < $level)
        {
            return false;
        }

        return true;
    }

    private static function getConfigLevel($type)
    {
        $config = self::getConfig();

        if (!isset($config))
        {
            return null;
        }

        if (!isset($config['type']))
        {
            return null;
        }

        $typeConfig = $config['type'];

        if (!isset($typeConfig[$type]))
        {
            return null;
        }

        return $typeConfig[$type];
    }

    private static function getLogDir($type)
    {
        $default = APP_SYS_PATH .'/runtime/log';

        $config = self::getConfig();

        if (!empty($config)
            && !empty($config['path'])
            && is_string($config['path'])
        )
        {
            $default = $config['path'];
        }

        $dirPath = "{$default}/{$type}";

        if (!file_exists($dirPath))
        {
            mkdir($dirPath, 0777, true);
        }

        return $dirPath;
    }
}