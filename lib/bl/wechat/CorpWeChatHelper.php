<?php
/**
 * 企业微信对象工厂
 */

namespace app\lib\bl\wechat;

use app\lib\vendor\wechat\CorpWeChat;
use app\lib\vendor\wechat\CorpWeChatWeb;
use app\lib\bl\Enum;

abstract class CorpWeChatHelper extends BaseHelper
{
    protected static $configName = 'corpWeChat';

    const TOKEN_NAME_ACCESS_TOKEN = 'corp_wechat_access_token';
    const TOKEN_NAME_JS_TICKET = 'corp_wechat_js_ticket';

    protected static $tokenMap = [
        self::TOKEN_NAME_ACCESS_TOKEN => null,
        self::TOKEN_NAME_JS_TICKET => null,
    ];

    /**
     * @return CorpWeChat
     * @throws \yii\web\BadRequestHttpException
     */
    public static function create()
    {
        $params = self::getConfig();
        $params['accessToken'] = self::getAccessToken();
        return new CorpWeChat($params);
    }

    /**
     * @return CorpWeChatWeb
     * @throws \yii\web\BadRequestHttpException
     */
    public static function createWeb()
    {
        $params = self::getConfig();
        $params['accessToken'] = self::getAccessToken();
        return new CorpWeChatWeb($params);
    }

    //-------------------------------------------------------------------------

    /**
     * @param $url
     * @return array
     * @throws \yii\web\BadRequestHttpException
     */
    public static function getJsSign($url)
    {
        $pos = strpos($url, '#');

        if ($pos !== FALSE)
        {
            $url = substr($url, 0, $pos);
        }

        $params = self::getConfig();
        return CorpWeChatWeb::getJsSign($url, self::getJsTicket(), $params['corpID']);
    }

    //------------------------------------------------------------------------

    /**
     * @return mixed
     * @throws \yii\web\BadRequestHttpException
     * @throws \Exception
     */
    public static function createAccessToken()
    {
        $params = self::getConfig();
        return CorpWeChat::getAccessToken($params['corpID'], $params['corpSecret']);
    }

    /**
     * @return mixed|null
     * @throws \Exception
     */
    public static function createJsTicket()
    {
        return CorpWeChat::getJsApiTicket(self::getAccessToken());
    }

    //------------------------------------------------------------------------

    public static function getAccessToken()
    {
        return self::getToken([
            'name' => self::TOKEN_NAME_ACCESS_TOKEN,
            'propType' => Enum::ADMIN_PM_TYPE_DATA,
            'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_TOKEN,
        ]);
    }

    /**
     * @param $value
     * @param $expires_in
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public static function saveAccessToken($value, $expires_in)
    {
        self::saveToken(
            [
                'name' => self::TOKEN_NAME_ACCESS_TOKEN,
                'desc' => self::TOKEN_NAME_ACCESS_TOKEN,
                'propType' => Enum::ADMIN_PM_TYPE_DATA,
                'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_TOKEN
            ],
            $value,
            $expires_in
        );
    }

    /**
     * @return mixed|null
     */
    public static function getJsTicket()
    {
        return self::getToken([
            'name' => self::TOKEN_NAME_JS_TICKET,
            'propType' => Enum::ADMIN_PM_TYPE_DATA,
            'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_JS_TICKET,
        ]);
    }

    /**
     * @param $value
     * @param $expires_in
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public static function saveJsTicket($value, $expires_in)
    {
        self::saveToken(
            [
                'name' => self::TOKEN_NAME_JS_TICKET,
                'desc' => self::TOKEN_NAME_JS_TICKET,
                'propType' => Enum::ADMIN_PM_TYPE_DATA,
                'propID' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_JS_TICKET
            ],
            $value,
            $expires_in
        );
    }
}