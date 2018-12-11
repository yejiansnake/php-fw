<?php
/**
 * 微信网页授权
 */

namespace app\lib\vendor\wechat;

class WeChatWeb extends WeChatWebBase
{
    //---------------------------------------------------------------------------------------------------
    //网页授权

    const URL_AUTHORIZE = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    //---------------------------------------------------------------------------------------------------
    //接口

    //通过code换取网页授权access_token
    const API_URL_ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/access_token';

    //刷新access_token
    const API_URL_REFRESH_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/refresh_token';

    //检验授权凭证（access_token）是否有效
    const API_URL_SNS_AUTH = 'https://api.weixin.qq.com/sns/auth';

    //---------------------------------------------------------------------------------------------------
    //参数名称

    private $appID = '';
    private $appSecret = '';

    //---------------------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['appID'])
            || empty($options['appSecret']))
        {
            throw new \InvalidArgumentException();
        }

        $this->appID = $options['appID'];
        $this->appSecret = $options['appSecret'];
    }

    public static function getJsSign($url, $jsTicket, $appID)
    {
        $params = [
            'noncestr' => uniqid('jsApiTicket'),
            'jsapi_ticket' => $jsTicket,
            'timestamp' => time(),
            'url' => $url
        ];

        ksort($params);
        $tmp = [];
        foreach ($params as $key => $value) {
            $tmp[] = "{$key}={$value}";
        }

        $str = implode('&', $tmp);
        $signature = sha1($str);

        return [
            'appId' => $appID,
            'nonceStr' => $params['noncestr'],
            'timestamp' => $params['timestamp'],
            'signature' => $signature,
            //'str' => $str
        ];
    }
    
    //---------------------------------------------------------------------------------------------------
    //微信 API 方法

    public function getAuthorizeUrl($redirectUrl, $state = null, $scopeType = self::SCOPE_TYPE_SNSAPI_BASE)
    {
        if (empty($state))
        {
            $state = '';
        }

        $scope = self::$scopeMap[self::SCOPE_TYPE_SNSAPI_BASE];

        if (array_key_exists($scopeType, self::$scopeMap))
        {
            $scope = self::$scopeMap[$scopeType];
        }

        $apiParams = [
            'appid' => $this->appID,
            'redirect_uri' =>$redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ];

        $strApiParams =  http_build_query($apiParams);

        $url = self::URL_AUTHORIZE . "?{$strApiParams}#wechat_redirect";

        return $url;
    }

    public function accessToken($code)
    {
        $params = [
            'appid' => $this->appID,
            'secret' => $this->appSecret,
            'code' => $code,
            'grant_type' => 'authorization_code',
        ];

        return parent::callApi(self::API_URL_ACCESS_TOKEN, $params);
    }

    public function refreshToken($refresh_token)
    {
        $params = [
            'appid' => $this->appID,
            'refresh_token' => $refresh_token,
            'grant_type' => 'refresh_token',
        ];

        return parent::callApi(self::API_URL_REFRESH_TOKEN, $params);
    }

    public static function snsAuth($access_token, $openid)
    {
        $params = [
            'access_token' => $access_token,
            'openid' => $openid,
        ];

        return self::callApi(self::API_URL_SNS_AUTH, $params);
    }
}