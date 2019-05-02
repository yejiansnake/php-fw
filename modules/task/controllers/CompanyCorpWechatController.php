<?php

namespace app\modules\task\controllers;

use app\models\admin\CompanyModel;
use app\lib\bl\wechat\CompanyCorpWeChatHelper;

class CompanyCorpWechatController extends BaseController
{
    public function actionUpdateToken()
    {
        $models = CompanyModel::get(['is_enable' => 1]);

        foreach ($models as $model)
        {
            CompanyCorpWeChatHelper::updateCorpToken($model->id);
        }
    }
}

