<?php

namespace app\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use app\lib\common\HttpHelper;
use app\lib\common\LoadMgr;
use app\lib\bl\session\CurUserSession;

class SiteController extends BaseController
{
    public function actionTest()
    {
        return 'test';
    }

    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;

        if (isset($params['r']))
        {
            unset($params['r']);
        }

        $url = '';

        if (CurUserSession::exist())
        {
            $url = HttpHelper::wrapUrl(LoadMgr::$curRoute['default'], $params);
        }
        else if (isset(LoadMgr::$curRoute['login']))
        {
            $url = HttpHelper::wrapUrl(LoadMgr::$curRoute['login'], $params);
        }
        else if (isset(LoadMgr::$curRoute['default']))
        {
            $url = HttpHelper::wrapUrl(LoadMgr::$curRoute['default'], $params);
        }
        else
        {
            throw new NotFoundHttpException('page not found');
        }

        return $this->redirect($url);
    }
}