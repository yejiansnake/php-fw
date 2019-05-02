<?php

namespace app\lib\bl;

use Yii;
use app\models\admin\CompanyModel;

class CompanyUrlHelper
{
    public static function getCommonDomainUrl()
    {
        $app_protocol = Yii::$app->params['app_protocol'];
        $baseHost = Yii::$app->params['baseHost'];

        return "{$app_protocol}://www.{$baseHost}";
    }

    public static function getMgrUrl($companyID)
    {
        $app_protocol = Yii::$app->params['app_protocol'];
        $baseHost = Yii::$app->params['baseHost'];

        $model = CompanyModel::getOne(['id' => $companyID]);

        if (empty($model))
        {
            return null;
        }

        return "{$app_protocol}://mgr-{$model->key_name}.{$baseHost}";
    }
	
    public static function getAppUrl($companyID)
    {
        $app_protocol = Yii::$app->params['app_protocol'];
        $baseHost = Yii::$app->params['baseHost'];

        $model = CompanyModel::getOne(['id' => $companyID]);

        if (empty($model))
        {
            return null;
        }

        return "{$app_protocol}://app-{$model->key_name}.{$baseHost}";
    }
}