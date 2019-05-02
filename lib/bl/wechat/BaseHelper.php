<?php
/**
 * 微信对象工厂基类
 */

namespace app\lib\bl\wechat;

use Yii;
use app\lib\bl\CachedMgr;
use app\lib\common\DateTimeEx;
use app\models\admin\SysPropsModel;
use yii\web\BadRequestHttpException;
use app\models\admin\CompanyAppTokenModel;

abstract class BaseHelper
{
    protected static $configName = '';
    protected static $tokenMap = [];

    protected static function getConfig()
    {
        if (empty(\Yii::$app->params[static::$configName]))
        {
            throw new BadRequestHttpException('config not exist');
        }

        return \Yii::$app->params[static::$configName];
    }

    protected static function getToken(array $params)
    {
        //从当前静态变量取
        if (!array_key_exists($params['name'], static::$tokenMap))
        {
            return static::$tokenMap[$params['name']];
        }

        //从缓存取
        $value = CachedMgr::get($params['name']);

        //还没有则从数据库取
        if (empty($value))
        {
            $model = SysPropsModel::getProp(
                $params['propType'],
                $params['propID']);

            if (empty($model))
            {
                return null;
            }

            $value = $model->value6;

            CachedMgr::set($params['name'], $value, strtotime($model->value8));
        }

        //写入当前临时变量
        static::$tokenMap[$params['name']] = $value;

        return $value;
    }

    protected static function saveToken(array $params, $value, $expires_in)
    {
        if (empty($params)
            ||empty($value)
            || empty($expires_in))
        {
            return;
        }

        //1.存入数据库
        SysPropsModel::setProp(
            [
                'type' => $params['propType'],
                'tid' => $params['propID'],
                'name' => $params['name'],
                'desc' => $params['desc'],
                'value1' => $expires_in,
                'value6' => $value,
                'value8' => DateTimeEx::getString(['change' => ["+{$expires_in} seconds"]]),
            ]);

        //2.存入缓存
        CachedMgr::set($params['name'], $value, $expires_in);

        //3.写入当前临时变量
        static::$tokenMap[$params['name']]= $value;
    }

    protected static function getClientKey(array $params)
    {
        return "{$params['name']}_{$params['clientID']}_{$params['type']}";
    }

    public static function getCompanyKey(array $params)
    {
        return "{$params['name']}_{$params['id']}_{$params['type']}";
    }

    protected static function getCompanyToken(array $params)
    {
        if (empty($params['name']) || empty($params['id']) || empty($params['type']))
        {
            return null;
        }

        $key = self::getCompanyKey($params);

        //1.从当前内存中获取
        if (array_key_exists($key, static::$tokenMap))
        {
            return static::$tokenMap[$key];
        }

        //2.从缓存取
        $info = \Yii::$app->cache->get($key);

        //3.还没有则从数据库取
        if (empty($info))
        {
            $model = CompanyAppTokenModel::getOne(['id' => $params['id'], 'type' => $params['type']]);

            if (empty($model))
            {
                return null;
            }

            $info = [
                'appID' => $model->app_id,
                'token' => $model->token,
                'refreshToken' => $model->refresh_token,
            ];

            \Yii::$app->cache->set($key, $info, strtotime($model->expires_at));
        }

        //存入缓存
        static::$tokenMap[$key] = $info;

        return $info;
    }

    protected static function saveCompanyToken(array $params, $value, $expires_in, $refreshValue = null)
    {
        if (empty($params)
            || empty($value)
            || empty($expires_in))
        {
            throw new BadRequestHttpException('config not exist');
        }

        if (empty($params['name'])
            || empty($params['id'])
            || empty($params['type'])
            || empty($params['appID']))
        {
            return null;
        }

        $key = self::getCompanyKey($params);

        //1.存入数据库
        CompanyAppTokenModel::saveOne([
            'id' => $params['id'],
            'type' => $params['type'],
            'app_id' => $params['appID'],
            'token' => $value,
            'expires_in' => $expires_in,
            'refresh_token' => $refreshValue,
        ]);

        $info = [
            'appID' => $params['appID'],
            'token' => $value,
            'refreshToken' => $refreshValue,
        ];

        //2.存入缓存
        \Yii::$app->cache->set($key, $info, $expires_in);

        //3.存储到内存
        static::$tokenMap[$key] = $info;
    }
}