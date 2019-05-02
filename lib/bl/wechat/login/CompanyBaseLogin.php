<?php

namespace app\lib\bl\wechat\login;

use app\models\admin\CompanyAppModel;
use Yii;
use yii\web\BadRequestHttpException;
use app\lib\common\Guid;

abstract class CompanyBaseLogin
{
    protected static $expireTime = 300;
    protected static $cacheKeyPrefix = 'WBL';
    protected static $configMap = [];

    protected static function getConfig($companyID)
    {
        if (empty(static::$configMap[$companyID]))
        {
            $model = CompanyAppModel::getOne(['id' => $companyID]);

            if (empty($model))
            {
                throw new BadRequestHttpException("comapny ID:{$companyID} not exist app config");
            }

            static::$configMap[$companyID] = $model->toArray(['id', 'app_id', 'agent_id', 'event_token', 'event_key']);
        }

        return static::$configMap[$companyID];
    }

    protected static function createPreCacheKey()
    {
        return self::createCacheKey('Pre');
    }

    protected static function createLoginCacheKey()
    {
        return self::createCacheKey('Login');
    }

    protected static function createCacheKey($add = '')
    {
        if (empty($add))
        {
            $add = '';
        }
        return static::$cacheKeyPrefix . $add . Guid::ToStringWeb($add);
    }
}