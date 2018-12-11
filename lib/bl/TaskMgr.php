<?php

namespace app\lib\bl;

use app\lib\common\SysCmd;

class TaskMgr
{
//    private static function runAsync($controllerName, $action, array $params = [])
//    {
//        $strParams = implode(' ', $params);
//        $cmd = "task/{$controllerName}/{$action} {$strParams}";
//        if (!SysCmd::callTask($cmd))
//        {
//            LogMgr::addSysLog(__METHOD__, LogMgr::LEVEL_ERROR, "call task failed, cmd:{$cmd}");
//        }
//    }

    public static function sendMessage($id)
    {
        $cmd = "task/send-message/index {$id}";
        return SysCmd::callTask($cmd);
    }

    public static function downloadAudio(array $params = [])
    {
        //提交异步逻辑（下载音频逻辑） 单个小题
        $cmd = "task/download-audio/index {$params['caseId']}";
        return SysCmd::callTask($cmd);
    }

//    public static function createTotalPdf(array $params = [])
//    {
//        $cmd = APP_SYS_PATH . '/yii task/report/create-total ' . $params['company_id'] . ' ' . $params['exam_id'];
//        return shell_exec($cmd);
//    }

    public static function avconv($sourceFile, $targetFile)
    {
        $cmd = "avconv -i '{$sourceFile}' '{$targetFile}'";

        if (!empty(\Yii::$app->params['avconv_sudo']))
        {
            $cmd = "sudo {$cmd}";
        }

        exec($cmd);

        return '';
    }
}