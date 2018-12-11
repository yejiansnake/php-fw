<?php

namespace app\lib\bl\wechat\login;

use Yii;
use yii\web\BadRequestHttpException;
use app\lib\common\Guid;

abstract class BaseLogin
{
    protected static $expireTime = 300;
    protected static $configKey = '';
    protected static $cacheKeyPrefix = 'WBL';

    protected static function getConfig()
    {
        $configKey = static::$configKey;

        if (empty(Yii::$app->params[$configKey]))
        {
            throw new BadRequestHttpException("config key:{$configKey} not exist");
        }

        return Yii::$app->params[$configKey];
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