<?php

namespace app\lib\common;

use Yii;
use \yii\base\Module;
use yii\web\UnauthorizedHttpException;
use yii\web\Response;

abstract class BaseApiModule extends Module
{
    protected $checkModule = true;

    public function init()
    {
        if (!empty($this->checkModule) && LoadMgr::isEnable())
        {
            if (LoadMgr::$sysModule != $this->id)
            {
                Yii::$app->response->format = Response::FORMAT_JSON;
                throw new UnauthorizedHttpException("没有权限访问");
            }
        }

        parent::init();
    }
}
