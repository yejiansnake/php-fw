<?php

namespace app\lib\common;

//./yii task/home/test

class SysCmd
{
    public static function callTask($taskRouteAndParams)
    {
        if (empty($taskRouteAndParams))
        {
            return false;
        }

        $cmd = 'php ' . APP_SYS_PATH . "/yii {$taskRouteAndParams}";
//        echo $cmd;
//        exit;
        return self::asyncExec($cmd);
    }

    public static function asyncExec($cmd, $outFile = null)
    {
        if (empty($cmd))
        {
            return false;
        }

        if (empty($outFile))
        {
            $outFile = '/dev/null';
            //system("{$cmd} >> /dev/null &");
        }

        if (false === system("{$cmd} >> {$outFile} &"))
        {
            return false;
        }

        return true;
    }
}