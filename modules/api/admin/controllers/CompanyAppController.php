<?php

namespace app\modules\api\admin\controllers;


use Yii;
use yii\web\BadRequestHttpException;
use app\lib\bl\wechat\CompanyCorpWeChatHelper;
use app\models\admin\CompanyAppModel;
use app\models\admin\CompanyAppTokenModel;

class CompanyAppController extends BaseController
{
    protected static $apiModelName = 'app\models\admin\CompanyAppModel';
    protected static $apiModelOptions = ['view', 'create', 'update', 'index'];

    public function actionSave()
    {
        $params = Yii::$app->request->bodyParams;
        if (empty($params)
            || empty($params['id'])
            || empty($params['app_id'])
            || empty($params['app_secret'])
            || empty($params['agent_id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $params['curUserID'] = $this->curUserID;
        return CompanyAppModel::saveOne($params);
    }

    public function actionGetAccessToken()
    {
        $company_id = Yii::$app->request->get('company_id');
        if (empty($company_id))
        {
            throw new BadRequestHttpException('params invalid');
        }


        return ['items' => self::getAccessToken($company_id)];
    }

    public function actionSetToken()
    {
        $params = Yii::$app->request->bodyParams;
        if (empty($params['company_id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        CompanyCorpWeChatHelper::updateCorpToken($params['company_id']);
        return ['items' => self::getAccessToken($params['company_id'])];
    }

    private static function getAccessToken($id)
    {
        if (empty($id))
        {
            throw new BadRequestHttpException('params invalid');
        }

        return CompanyAppTokenModel::get(['id' => $id]);
    }
}