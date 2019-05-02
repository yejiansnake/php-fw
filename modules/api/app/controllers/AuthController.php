<?php

namespace app\modules\api\app\controllers;

use Yii;
use yii\web\BadRequestHttpException;

class AuthController extends AuthCompanyCorpWeChatController
{
    public function actionLogin()
    {
        if (!empty(Yii::$app->params['suspend']))
        {
            throw new BadRequestHttpException("系统维护中，请稍后再试");
        }

        return parent::actionLogin();
    }
}