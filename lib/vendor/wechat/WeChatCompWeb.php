<?php
/**
 * 微信网页授权
 */

namespace app\lib\vendor\wechat;

class WeChatCompWeb extends WeChatWebBase
{
    const SCOPE_TYPE_SNSAPI_BASE = 1;
    const SCOPE_TYPE_SNSAPI_USERINFO = 2;

    protected static $scopeMap = [
        self::SCOPE_TYPE_SNSAPI_BASE => 'snsapi_base',
        self::SCOPE_TYPE_SNSAPI_USERINFO => 'snsapi_userinfo',
    ];

    //---------------------------------------------------------------------------------------------------
    //网页授权

    //授权成功后返回：redirect_uri?code=CODE&state=STATE&appid=APPID
    const URL_AUTHORIZE = 'https://open.weixin.qq.com/connect/oauth2/authorize';

    //---------------------------------------------------------------------------------------------------
    //接口

    //通过code换取网页授权access_token
    const API_URL_ACCESS_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/component/access_token';

    //刷新access_token
    const API_URL_REFRESH_TOKEN = 'https://api.weixin.qq.com/sns/oauth2/component/refresh_token';

    //---------------------------------------------------------------------------------------------------
    //参数名称

    private $clientAppID = '';
    private $componentAppID = '';
    private $componentAccessToken = '';

    //---------------------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['client_appID'])
            || empty($options['component_AppID'])
            || empty($options['component_access_token']))
        {
            throw new \InvalidArgumentException();
        }

        $this->clientAppID = $options['client_appID'];
        $this->componentAppID = $options['component_AppID'];
        $this->componentAccessToken = $options['component_access_token'];
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

    public function getAuthorizeUrl($redirectUrl, $state = null, $scoreType = 1)
    {
        if (empty($state))
        {
            $state = '';
        }

        $scope = self::$scopeMap[self::SCOPE_TYPE_SNSAPI_BASE];

        if (array_key_exists($scoreType, self::$scopeMap))
        {
            $scope = self::$scopeMap[$scoreType];
        }

        $apiParams = [
            'appid' => $this->clientAppID,
            'redirect_uri' =>$redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
            'component_appid' => $this->componentAppID,
        ];

        $strApiParams =  http_build_query($apiParams);

        $url = self::URL_AUTHORIZE . "?{$strApiParams}#wechat_redirect";

        return $url;
    }

    public function accessToken($code)
    {
        $params = [
            'appid' => $this->clientAppID,
            'code' => $code,
            'grant_type' => 'authorization_code',
            'component_appid' => $this->componentAppID,
            'component_access_token' => $this->componentAccessToken,
        ];

        return parent::callApi(self::API_URL_ACCESS_TOKEN, $params);
    }

    public function refreshToken($clientRefreshToken)
    {
        $params = [
            'appid' => $this->clientAppID,
            'grant_type' => 'authorization_code',
            'refresh_token' => $clientRefreshToken,
            'component_appid' => $this->componentAppID,
            'component_access_token' => $this->componentAccessToken,
        ];

        return parent::callApi(self::API_URL_REFRESH_TOKEN, $params);
    }
}