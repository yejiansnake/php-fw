<?php

namespace app\modules\api\admin\controllers;

use Yii;
use app\lib\bl\AuthTrait;
use app\models\admin\CorpAuthUserModel;
use app\lib\bl\wechat\login\CorpWeChatWebLogin;
use yii\web\NotFoundHttpException;

abstract class AuthCorpWeChatWebController extends BaseController
{
    use AuthTrait;

    public function actionPre()
    {
        return CorpWeChatWebLogin::handlePre();
    }

    public function actionLogin()
    {
        $userInfo = CorpWeChatWebLogin::handleLogin();
        $model = CorpAuthUserModel::getOne(['wx_id' => $userInfo['userid'], 'is_enable' => 1]);
        if (empty($model))
        {
            throw new NotFoundHttpException('您没有后台操作权限，请联系管理员');
        }

        $userInfo['roles'] = [];
        $userInfo['permissions'] = [];

        $this->resetCurUser($userInfo);

        return $userInfo;
    }
}