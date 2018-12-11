<?php
/**
 * 微信网页授权基类
 */

namespace app\lib\vendor\wechat;

abstract class WeChatWebBase extends BaseWeChat
{
    const SCOPE_TYPE_SNSAPI_BASE = 1;
    const SCOPE_TYPE_SNSAPI_USERINFO = 2;

    protected static $scopeMap = [
        self::SCOPE_TYPE_SNSAPI_BASE => 'snsapi_base',
        self::SCOPE_TYPE_SNSAPI_USERINFO => 'snsapi_userinfo',
    ];

    //---------------------------------------------------------------------------------------------------
    //接口

    const API_URL_USER_INFO = 'https://api.weixin.qq.com/sns/userinfo';                 //拉取用户信息(需scope为 snsapi_userinfo)

    //---------------------------------------------------------------------------------------------------
    //微信 API 方法

    public static function snsUserInfo($access_token, $openid)
    {
        $params = [
            'access_token' => $access_token,
            'openid' => $openid,
            'lang' => 'zh_CN',
        ];

        return self::callApi(self::API_URL_USER_INFO, $params);
    }
}