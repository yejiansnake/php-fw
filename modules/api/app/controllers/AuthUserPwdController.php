<?php

namespace app\modules\api\app\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use app\lib\bl\session\CurUserSession;
use app\lib\bl\AuthTrait;
use app\models\client\AuthUserModel;

abstract class AuthUserPwdController extends BaseController
{
    use AuthTrait;

    public function actionLogin()
    {
        $data = Yii::$app->request->bodyParams;

        if (empty($data['name'])
            || empty($data['pwd']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        if (CurUserSession::exist())
        {
            CurUserSession::clear();
        }

        $model = AuthUserModel::getOne([
            'name' => $data['name'],
            'pwd' => $data['pwd'],
            'is_enable' => 1,
        ]);

        if (empty($model))
        {
            throw new BadRequestHttpException('user name or password error');
        }

        $userInfo = $model->toArray();
        $userInfo['roles'] = [];
        $userInfo['permissions'] = [];

        $this->resetCurUser($userInfo);

        return $userInfo;
    }
}