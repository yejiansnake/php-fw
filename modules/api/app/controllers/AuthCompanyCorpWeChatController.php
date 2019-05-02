<?php

namespace app\modules\api\app\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use app\lib\bl\AuthTrait;
use app\lib\bl\wechat\CompanyCorpWeChatHelper;
use app\lib\bl\wechat\login\CompanyCorpWeChatLogin;
use app\models\client\CorpAppUserModel;
use yii\web\ServerErrorHttpException;

abstract class AuthCompanyCorpWeChatController extends BaseController
{
    use AuthTrait;

    public function actionPre()
    {
        return CompanyCorpWeChatLogin::handlePre($this->curCompanyID);
    }

    public function actionLogin()
    {
        $userInfo = CompanyCorpWeChatLogin::handleLogin($this->curCompanyID);

        if (empty($userInfo))
        {
            throw new ServerErrorHttpException('user login failed');
        }

        $model = CorpAppUserModel::saveOneFromApi($userInfo);

        if (empty($model))
        {
            throw new BadRequestHttpException('您没有访问权限，请联系管理员');
        }

        $userInfo['id'] = $model->id;
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

        $url = urldecode($params['url']);

        return [
            'items' => [
                'url' => $url,
                'wxJs' => CompanyCorpWeChatHelper::getJsSign($this->curCompanyID, $url)
            ]
        ];
    }
}