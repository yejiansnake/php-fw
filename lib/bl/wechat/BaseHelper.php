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

abstract class BaseHelper
{
    protected static $configName = '';
    protected static $tokenMap = [];

    /**
     * @return mixed
     * @throws BadRequestHttpException
     */
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

    /**
     * @param array $params
     * @param $value
     * @param $expires_in
     * @throws BadRequestHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
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
                'id' => $params['propID'],
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
}