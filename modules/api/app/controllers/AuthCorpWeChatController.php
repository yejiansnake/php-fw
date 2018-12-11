<?php

namespace app\modules\api\app\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use app\lib\bl\wechat\CorpWeChatHelper;
use app\models\client\CorpUserModel;
use app\lib\bl\AuthTrait;
use app\lib\bl\wechat\login\CorpWeChatLogin;

abstract class AuthCorpWeChatController extends BaseController
{
    use AuthTrait;

    public function actionPre()
    {
        return CorpWeChatLogin::handlePre();
    }

    public function actionLogin()
    {
        $userInfo = CorpWeChatLogin::handleLogin();
        $model = CorpUserModel::getOne(['wx_id' => $userInfo['userid']]);

        if (empty($model))
        {
            throw new NotFoundHttpException('您没有操作权限，请联系管理员');
        }

        $userInfo['id'] = $model->id;
        $userInfo['roles'] = [];
        $userInfo['permissions'] = [];
        $this->resetCurUser($userInfo);
        return $userInfo;
    }

    public function actionBase()
    {
        $params = Yii::$app->request->queryParams;

        if (empty($params['url']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $url = $sourceUrl = urldecode($params['url']);
        return [
            'items' => [
                'url' => $url,
                'wxJs' => CorpWeChatHelper::getJsSign($url)
            ]
        ];
    }
}