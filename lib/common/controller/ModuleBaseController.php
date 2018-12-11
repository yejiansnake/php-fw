<?php

namespace app\lib\common\controller;

use Yii;
use yii\web\Controller;
use yii\web\BadRequestHttpException;
use app\lib\common\LoadMgr;

abstract class ModuleBaseController extends Controller
{
    protected $moduleName = null;
    public $layout = false;

    public function beforeAction($action)
    {
        $res = parent::beforeAction($action);

        if ($res)
        {
            if (LoadMgr::$sysModule != $this->moduleName
                || empty($this->moduleName)
                || empty(LoadMgr::$sysModule))
            {
                throw new BadRequestHttpException('module invalid');
            }
        }

        return $res;
    }
}