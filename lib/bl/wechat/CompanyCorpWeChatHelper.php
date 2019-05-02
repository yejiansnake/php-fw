<?php

namespace app\lib\bl\wechat;

use app\lib\bl\Enum;
use app\lib\vendor\wechat\CorpWeChat;
use app\lib\vendor\wechat\CorpWeChatEvent;
use app\lib\vendor\wechat\CorpWeChatWeb;
use app\models\admin\CompanyAppModel;

abstract class CompanyCorpWeChatHelper extends BaseHelper
{
    const TOKEN_NAME_ACCESS_TOKEN = 'cp_corp_wechat_access_token';
    const TOKEN_NAME_JS_TICKET = 'cp_corp_wechat_js_ticket';

    public static function create($id)
    {
        return new CorpWeChat(self::getCompanyAccessInfo($id));
    }

    public static function createWeb($id)
    {
        return new CorpWeChatWeb(self::getCompanyAccessInfo($id));
    }

    public static function createEvent(array $params)
    {
        return new CorpWeChatEvent($params);
    }

    public static function getCompanyAccessInfo($id)
    {
        $info = self::getAccessToken($id);

        if (empty($info))
        {
            throw new \Exception('not company app token');
        }

        $params['corpID'] = $info['appID'];
        $params['accessToken'] = $info['token'];

        return $params;
    }

    //-------------------------------------------------------------------------

    public static function getJsSign($id, $url)
    {
        $pos = strpos($url, '#');

        if ($pos !== FALSE)
        {
            $url = substr($url, 0, $pos);
        }

        $params = self::getJsTicket($id);
        return CorpWeChatWeb::getJsSign($url, $params['token'], $params['appID']);
    }

    //------------------------------------------------------------------------

    public static function updateCorpToken($id)
    {
        $model = CompanyAppModel::getOne(['id' => $id]);

        if (empty($model))
        {
            return;
        }

        $res = self::createAccessToken([
            'corpID' => $model->app_id,
            'secret' => $model->app_secret,
        ]);

        if (empty($res))
        {
            return;
        }

        self::saveAccessToken($id, $model->app_id, $res['access_token'], $res['expires_in']);

        //js ticket

        $res = self::createJsTicket($res['access_token']);

        if (empty($res))
        {
            return;
        }

        self::saveJsTicket($id, $model->app_id, $res['ticket'], $res['expires_in']);
    }

    private static function createAccessToken($params)
    {
        return CorpWeChat::getAccessToken($params['corpID'], $params['secret']);
    }

    private static function createJsTicket($accessToken)
    {
        return CorpWeChat::getJsApiTicket($accessToken);
    }

    //------------------------------------------------------------------------

    public static function getAccessToken($id)
    {
        return self::getCompanyToken([
            'name' => self::TOKEN_NAME_ACCESS_TOKEN,
            'id' => $id,
            'type' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_TOKEN,
        ]);
    }

    public static function saveAccessToken($id, $appID, $value, $expires_in)
    {
        self::saveCompanyToken(
            [
                'name' => self::TOKEN_NAME_ACCESS_TOKEN,
                'id' => $id,
                'appID' => $appID,
                'type' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_TOKEN
            ],
            $value,
            $expires_in
        );
    }

    public static function getJsTicket($id)
    {
        return self::getCompanyToken([
            'name' => self::TOKEN_NAME_JS_TICKET,
            'id' => $id,
            'type' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_JS_TICKET,
        ]);
    }

    public static function saveJsTicket($id, $appID, $value, $expires_in)
    {
        self::saveCompanyToken(
            [
                'name' => self::TOKEN_NAME_JS_TICKET,
                'id' => $id,
                'appID' => $appID,
                'type' => Enum::ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_JS_TICKET
            ],
            $value,
            $expires_in
        );
    }
}