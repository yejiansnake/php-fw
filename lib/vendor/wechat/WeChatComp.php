<?php
/**
 * 微信第三方平台功能类
 */

namespace app\lib\vendor\wechat;

use yii\web\BadRequestHttpException;

class WeChatComp extends BaseWeChat
{
    //---------------------------------------------------------------------------------------------------
    //应用授权

    //从应用提供商网站发起授权（构造授权URL）
    const URL_AUTHORIZE = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage';

    //---------------------------------------------------------------------------------------------------
    //接口

    //获取第三方平台component_access_token
    const API_URL_COMP_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';

    //获取预授权码pre_auth_code
    const API_URL_CREATE_PRE_AUTH_CODE = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode';

    //使用授权码换取公众号或小程序的接口调用凭据和授权信息
    const API_URL_QUERY_AUTH = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth';

    //获取（刷新）授权公众号或小程序的接口调用凭据（令牌）
    const API_URL_AUTHORIZER_TOKEN = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token';

    //获取授权方的帐号基本信息
    const API_URL_GET_AUTHORIZER_INFO = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info';

    //获取授权方的选项设置信息
    const API_URL_GET_AUTHORIZER_OPTION = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_option';

    //设置授权方的选项信息
    const API_URL_SET_AUTHORIZER_OPTION = 'https://api.weixin.qq.com/cgi-bin/component/api_set_authorizer_option';

    //获取公众号 JS_API_TICKET
    const API_URL_GET_JS_API_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    //获取当前所有已授权的帐号基本信息
    const API_URL_GET_AUTHORIZER_LIST = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_list';

    //---------------------------------------------------------------------------------------------------

    private $appID = '';
    private $component_access_token = '';

    protected function init(array $options)
    {
        if (empty($options['appID'])
            || empty($options['component_access_token']))
        {
            throw new \InvalidArgumentException();
        }

        $this->appID = $options['appID'];
        $this->component_access_token = $options['component_access_token'];
    }

    //---------------------------------------------------------------------------------------------------
    //应用授权

    public function getAuthUrl($redirectUrl, $preAuthCode)
    {
        if (empty($redirectUrl) || empty($preAuthCode))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $apiParams = [
            'component_appid' => $this->appID,
            'pre_auth_code' => $preAuthCode,
            'redirect_uri' => $redirectUrl,
        ];

        $strApiParams =  http_build_query($apiParams);

        $url = self::URL_AUTHORIZE . "?{$strApiParams}";

        return $url;
    }

    //---------------------------------------------------------------------------------------------------

    public static function getCompAccessToken($compAppID, $compSecret, $compTicket)
    {
        $params = [
            'component_appid' => $compAppID,
            'component_appsecret' => $compSecret,
            'component_verify_ticket' => $compTicket,
        ];

        return self::callApiPostJson(self::API_URL_COMP_TOKEN, $params);
    }

    public static function getJsTicket($authorizerAccessToken)
    {
        if (empty($authorizerAccessToken))
        {
            return null;
        }

        $params = [
            'access_token' => $authorizerAccessToken,
            'type' => 'jsapi',
        ];

        return self::callApi(self::API_URL_GET_JS_API_TICKET, $params);
    }

    public function createPreAuthCode()
    {
        $getParams = [
            'component_access_token' => $this->component_access_token
        ];

        $params = [
            'component_appid' => $this->appID,
        ];

        return self::callApiPostJson(self::API_URL_CREATE_PRE_AUTH_CODE, $params, $getParams);
    }

    public function queryAuth($code)
    {
        $getParams = [
            'component_access_token' => $this->component_access_token
        ];

        $params = [
            'component_appid' => $this->appID,
            'authorization_code' => $code,
        ];

        return self::callApiPostJson(self::API_URL_QUERY_AUTH, $params, $getParams);
    }

    public function authorizerToken($authorizerAppID, $authorizerRefreshToken)
    {
        $getParams = [
            'component_access_token' => $this->component_access_token
        ];

        $params = [
            'component_appid' => $this->appID,
            'authorizer_appid' => $authorizerAppID,
            'authorizer_refresh_token' => $authorizerRefreshToken,
        ];

        return self::callApiPostJson(self::API_URL_AUTHORIZER_TOKEN, $params, $getParams);
    }

    public function getAuthorizerInfo($authorizerAppID)
    {
        $getParams = [
            'component_access_token' => $this->component_access_token
        ];

        $params = [
            'component_appid' => $this->appID,
            'authorizer_appid' => $authorizerAppID,
        ];

        return self::callApiPostJson(self::API_URL_GET_AUTHORIZER_INFO, $params, $getParams);
    }

    public function getAuthorizerOption($authorizerAppID, $optionName)
    {
        $getParams = [
            'component_access_token' => $this->component_access_token
        ];

        $params = [
            'component_appid' => $this->appID,
            'authorizer_appid' => $authorizerAppID,
            'option_name' => $optionName,
        ];

        return self::callApiPostJson(self::API_URL_GET_AUTHORIZER_OPTION, $params, $getParams);
    }

    public function setAuthorizerOption($authorizerAppID, $optionName, $optionValue)
    {
        $getParams = [
            'component_access_token' => $this->component_access_token
        ];

        $params = [
            'component_appid' => $this->appID,
            'authorizer_appid' => $authorizerAppID,
            'option_name' => $optionName,
            'option_value' => $optionValue,
        ];

        return self::callApiPostJson(self::API_URL_SET_AUTHORIZER_OPTION, $params, $getParams);
    }

    public function getAuthorizerList($offset = 0, $count = 100)
    {
        $getParams = [
            'component_access_token' => $this->component_access_token
        ];

        $params = [
            'component_appid' => $this->appID,
            'offset' => $offset,
            'count' => $count,
        ];

        return self::callApiPostJson(self::API_URL_GET_AUTHORIZER_LIST, $params, $getParams);
    }

}