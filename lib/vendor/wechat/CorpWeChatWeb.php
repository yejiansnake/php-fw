<?php
/**
 * 微信网页授权
 */

namespace app\lib\vendor\wechat;

class CorpWeChatWeb extends BaseWeChat
{
    const SCOPE_TYPE_SNSAPI_BASE = 1;           //静默授权，可获取成员的基础信息
    const SCOPE_TYPE_SNSAPI_USERINFO = 2;       //静默授权，可获取成员的详细信息，但不包含手机、邮箱
    const SCOPE_TYPE_SNSAPI_PRIVATEINFO = 3;    //手动授权，可获取成员的详细信息，包含手机、邮箱

    private static $scopeMap = [
        self::SCOPE_TYPE_SNSAPI_BASE => 'snsapi_base',
        self::SCOPE_TYPE_SNSAPI_USERINFO => 'snsapi_userinfo',
        self::SCOPE_TYPE_SNSAPI_PRIVATEINFO => 'snsapi_privateinfo',
    ];

    //---------------------------------------------------------------------------------------------------
    //网页授权

    const URL_AUTHORIZE = 'https://open.weixin.qq.com/connect/oauth2/authorize';    //用户同意授权，获取code


    //获取临时素材文件
    const API_URL_GET_MEDIA = 'https://qyapi.weixin.qq.com/cgi-bin/media/get';
    //---------------------------------------------------------------------------------------------------
    //接口

    const API_URL_GET_USER_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo';       //根据code获取成员信息
    const API_URL_GET_USER_DETAIL = 'https://qyapi.weixin.qq.com/cgi-bin/user/getuserdetail';   //使用user_ticket获取成员详情

    //---------------------------------------------------------------------------------------------------
    //参数名称

    private $corpID = '';
    private $accessToken = '';

    //---------------------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['corpID'])
            || empty($options['accessToken'])
        )
        {
            throw new \InvalidArgumentException();
        }

        $this->corpID = $options['corpID'];
        $this->accessToken = $options['accessToken'];
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
        foreach ($params as $key => $value)
        {
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
    //网页授权

    public function getAuthorizeUrl($redirectUrl, $state = null, $scoreType = 1, $agentID = null)
    {
        if (empty($state))
        {
            $state = '';
        }

        $scope = self::$scopeMap[self::SCOPE_TYPE_SNSAPI_BASE];

//        echo "{$scoreType}--------";
//        print_r(self::$scopeMap);
//        exit;

        if (array_key_exists($scoreType, self::$scopeMap))
        {
            $scope = self::$scopeMap[$scoreType];
        }

        $apiParams = [
            'appid' => $this->corpID,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ];

        if (!empty($agentID))
        {
            $apiParams['agentid'] = $agentID;
        }

        $strApiParams = http_build_query($apiParams);

        $url = self::URL_AUTHORIZE . "?{$strApiParams}#wechat_redirect";
        return $url;
    }

    //---------------------------------------------------------------------------------------------------
    //微信 API 方法

    public function getUserInfo($code)
    {
        $params = [
            'access_token' => $this->accessToken,
            'code' => $code,
        ];

        return parent::callApi(self::API_URL_GET_USER_INFO, $params);
    }

    public function getUserDetail($user_ticket)
    {
        $get = [
            'access_token' => $this->accessToken,
        ];

        $post = [
            'user_ticket' => $user_ticket,
        ];

        return parent::callApiPostJson(self::API_URL_GET_USER_DETAIL, $post, $get);
    }


    //获取临时素材文件

    /**
     * @param $mediaID
     * @return mixed
     * @throws \Exception
     */
    public function getMedia($mediaID)
    {
        $get = [
            'access_token' => $this->accessToken,
            'media_id' => $mediaID,
        ];

        $res = parent::callApiSource(self::API_URL_GET_MEDIA, $get);
        if (empty($res['data']))
        {
            throw new \Exception('data is empty');
        }

        return $res['data'];
    }

}