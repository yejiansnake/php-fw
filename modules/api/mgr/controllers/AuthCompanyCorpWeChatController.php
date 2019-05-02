<?php

namespace app\modules\api\mgr\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use app\lib\bl\AuthTrait;
use app\lib\bl\wechat\login\CompanyCorpWeChatWebLogin;
use app\lib\bl\wechat\CompanyCorpWeChatHelper;
use app\models\client\CorpAuthUserModel;

abstract class AuthCompanyCorpWeChatController extends BaseController
{
    use AuthTrait;

    public function actionPre()
    {
        return CompanyCorpWeChatWebLogin::handlePre($this->curCompanyID);
    }

    public function actionLogin()
    {
        $userInfo = CompanyCorpWeChatWebLogin::handleLogin($this->curCompanyID);
        $model = CorpAuthUserModel::getOne(['wx_id' => $userInfo['userid'], 'is_enable' => 1]);

        if (empty($model))
        {
            throw new BadRequestHttpException('您没有访问权限，请联系管理员');
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

        $url = urldecode($params['url']);

        return [
            'items' => [
                'url' => $url,
                'wxJs' => CompanyCorpWeChatHelper::getJsSign($this->curCompanyID, $url)
            ]
        ];
    }
}