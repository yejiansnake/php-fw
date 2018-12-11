<?php
/**
 * 微信对象工厂
 */

namespace app\lib\bl\wechat;

use Yii;
use app\lib\bl\Enum;
use app\lib\vendor\wechat\WeChat;
use app\lib\vendor\wechat\WeChatEvent;
use app\lib\vendor\wechat\WeChatPay;
use app\lib\vendor\wechat\WeChatWeb;
use app\lib\vendor\wechat\WeChatCorpPay;

abstract class WeChatHelper extends BaseHelper
{
    protected static $configName = 'weChat';

    const TOKEN_NAME_ACCESS_TOKEN = 'wechat_access_token';
    const TOKEN_NAME_JS_TICKET = 'wechat_js_ticket';

    protected static $tokenMap = [
        self::TOKEN_NAME_ACCESS_TOKEN => null,
        self::TOKEN_NAME_JS_TICKET => null,
    ];

    public static function isEnableCorpPay()
    {
        if (empty(Yii::$app->params['weChat'])
            || empty(Yii::$app->params['weChat']['pay']))
        {
            return false;
        }

        return true;
    }

    public static function create()
    {
        $params = self::getConfig();
        $params['access_token'] = self::getAccessToken();
        return new WeChat($params);
    }

    public static function createEvent()
    {
        return new WeChatEvent(\Yii::$app->params['weChat']);
    }

    public static function createWeb()
    {
        return new WeChatWeb(\Yii::$app->params['weChat']);
    }

    public static function createPay()
    {
        if (empty(\Yii::$app->params['weChat']))
        {
            throw new \Exception('weChat config not found');
        }

        $config = self::getConfig();

        if (empty($config['appID'])
            || empty($config['pay']))
        {
            throw new \Exception('weChat config not found: appID, pay');
        }

        $payConfig = $config['pay'];

        if (empty($payConfig['mchID'])
            || empty($payConfig['apiKey'])
            || empty($payConfig['certPath'])
            || empty($payConfig['notifyUrl']))
        {
            throw new \Exception('weChat pay config not found: mchID, apiKey, certPath');
        }

        $params = [
            'appID' => $config['appID'],
            'certPath' => $payConfig['certPath'],
            'mchID' => $payConfig['mchID'],
            'apiKey' => $payConfig['apiKey'],
            'notifyUrl' => $payConfig['notifyUrl'],
        ];

        return new WeChatPay($params);
    }

    public static function createCorpPay()
    {
        if (empty(\Yii::$app->params['weChat']))
        {
            throw new \Exception('weChat config not found');
        }

        $config = self::getConfig();

        if (empty($config['appID'])
            || empty($config['pay']))
        {
            throw new \Exception('weChat config not found: appID, pay');
        }

        $payConfig = $config['pay'];

        if (empty($payConfig['mchID'])
            || empty($payConfig['apiKey'])
            || empty($payConfig['certPath']))
        {
            throw new \Exception('weChat pay config not found: mchID, apiKey, certPath');
        }

        $params = [
            'appID' => $config['appID'],
            'certPath' => $payConfig['certPath'],
            'mchID' => $payConfig['mchID'],
            'apiKey' => $payConfig['apiKey'],
        ];

        return new WeChatCorpPay($params);
    }

    //-------------------------------------------------------------------------

    public static function getJsSign($url)
    {
        $pos = strpos($url, '#');

        if ($pos !== FALSE)
        {
            $url = substr($url, 0, $pos);
        }

        $params = Yii::$app->params['weChat'];
        return WeChatWeb::getJsSign($url, self::getJsApiTicket(), $params['appID']);
    }

    //-------------------------------------------------------------------------

    public static function createAccessToken()
    {
        $params = self::getConfig();
        return WeChat::getAccessToken($params['appID'], $params['appSecret']);
    }

    public static function createJsTicket()
    {
        return WeChat::getJsApiTicket(self::getAccessToken());
    }

    //-------------------------------------------------------------------------

    public static function getAccessToken()
    {
        return self::getToken([
            'name' => self::TOKEN_NAME_ACCESS_TOKEN,
            'propType' => Enum::ADMIN_PM_TYPE_DATA,
            'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_WECHAT_TOKEN,
        ]);
    }

    public static function saveAccessToken($value, $expires_in)
    {
        self::saveToken(
            [
                'name' => self::TOKEN_NAME_ACCESS_TOKEN,
                'desc' => self::TOKEN_NAME_ACCESS_TOKEN,
                'propType' => Enum::ADMIN_PM_TYPE_DATA,
                'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_WECHAT_TOKEN
            ],
            $value,
            $expires_in
        );
    }

    public static function getJsApiTicket()
    {
        return self::getToken([
            'name' => self::TOKEN_NAME_JS_TICKET,
            'propType' => Enum::ADMIN_PM_TYPE_DATA,
            'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_WECHAT_JS_TICKET,
        ]);
    }

    public static function saveJsTicket($value, $expires_in)
    {
        self::saveToken(
            [
                'name' => self::TOKEN_NAME_JS_TICKET,
                'desc' => self::TOKEN_NAME_JS_TICKET,
                'propType' => Enum::ADMIN_PM_TYPE_DATA,
                'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_WECHAT_JS_TICKET
            ],
            $value,
            $expires_in
        );
    }
}