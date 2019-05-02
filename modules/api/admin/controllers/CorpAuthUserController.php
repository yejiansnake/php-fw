<?php

namespace app\modules\api\admin\controllers;

use app\lib\bl\wechat\CorpWeChatHelper;
use app\models\admin\CorpAuthUserModel;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class CorpAuthUserController extends BaseController
{
    protected static $apiModelName = 'app\models\admin\CorpAuthUserModel';
    protected static $apiModelOptions = ['index', 'update', 'view'];    //API支持的基础接口

    public function actionCreate()
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['wx_id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $weChatHelper = CorpWeChatHelper::create();

        $userInfo = $weChatHelper->getUser($params['wx_id']);

        if (!empty($userInfo['errcode']))
        {
            throw new ServerErrorHttpException("获取用户失败, 消息:{$userInfo['errmsg']}");
        }

        $model = CorpAuthUserModel::saveOne([
            'wx_id' => $userInfo['userid'],
            'name' => $userInfo['name'],
        ]);

        return $model;
    }
}