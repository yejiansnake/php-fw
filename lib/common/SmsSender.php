<?php

namespace app\lib\common;

use Yii;
use yii\web\ServerErrorHttpException;
use app\lib\vendor\QCloud\SmsClient;

class SmsSender
{
    private static $client = null;

    private static function client()
    {
        if (empty(self::$client))
        {
            if (empty(Yii::$app->params['sms'])
                || empty(Yii::$app->params['sms']['appId'])
                || empty(Yii::$app->params['sms']['appKey'])
            )
            {
                throw new ServerErrorHttpException('config invalid');
            }

            self::$client = new SmsClient(Yii::$app->params['sms']);
        }

        return self::$client;
    }

    // 单条使用模板
    // 电话号码 ， 模板id , 模板替换参数（数组） , 期望返回值（默认空）
    public static function SingleTemplate($number, $tplId, $params, $ext = '')
    {
        $client = self::client();
        $phone = self::getPhone($number);
        return $client->sendSingleTemplate($phone['code'], $phone['mobile'],
            $tplId, $params, "", "", $ext);
    }

    // 单条直接发送消息
    // 电话号码 ， 消息 , 期望返回值（默认空） , 短信类型（默认0） ,
    public static function Single($number, $msg, $ext = '', $type = 0)
    {
        $client = self::client();
        $phone = self::getPhone($number);
        return $client->sendSingle($phone['code'], $phone['mobile'], $msg, $type, "", $ext);
    }

    private static function getPhone($number)
    {
        $nationCode = '';
        $mobile = '';

        $tmp = explode('-', $number);
        if (count($tmp) == 1)
        {
            $nationCode = '86';
            $mobile = trim($tmp[0]);
        }
        else
        {
            $nationCode = substr(trim($tmp[0]), 1);
            $mobile = trim($tmp[1]);
        }

        return [
            'code' => $nationCode,
            'mobile' => $mobile,
        ];
    }
}