<?php
/**
 * 微信第三方开发者授权接口
 */

namespace app\lib\vendor\wechat;

use yii\web\BadRequestHttpException;

class CorpWeChatSuite extends BaseWeChat
{
    //---------------------------------------------------------------------------------------------------
    //应用授权

    //从应用提供商网站发起授权（构造授权URL）
    const URL_CORP_AUTHORIZE = 'https://qy.weixin.qq.com/cgi-bin/loginpage';

    //---------------------------------------------------------------------------------------------------
    //接口

    //获取套件 token
    const API_URL_GET_SUITE_ACCESS_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_suite_token';

    //获取预授权码
    const API_URL_CREATE_PRE_AUTH_CODE = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_pre_auth_code';

    //设置授权配置
    const API_URL_SET_SESSION_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/service/set_session_info';

    //获取企业号的永久授权码
    const API_URL_GET_PERMANENT_CODE = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_permanent_code';

    //获取企业号的授权信息（使用永久授权码）
    const API_URL_GET_AUTH_INFO = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_auth_info';

    //获取企业号 access_token（使用永久授权码）
    const API_URL_GET_CORP_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/service/get_corp_token';

    //---------------------------------------------------------------------------------------------------
    //参数名称

    protected $suiteID = '';
    protected $suiteAccessToken = '';

    //---------------------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['suiteID'])
            || empty($options['suiteAccessToken']))
        {
            throw new \InvalidArgumentException();
        }

        $this->suiteID = $options['suiteID'];
        $this->suiteAccessToken = $options['suiteAccessToken'];
    }

    //---------------------------------------------------------------------------------------------------
    //应用授权

    public function getAuthUrl($redirectUrl, $preAuthCode, $state = '')
    {
        if (empty($redirectUrl) || empty($preAuthCode))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $apiParams = [
            'suite_id' => $this->suiteID,
            'pre_auth_code' => $preAuthCode,
            'redirect_uri' => $redirectUrl,
            'state' => $state,
        ];

        $strApiParams =  http_build_query($apiParams);

        $url = self::URL_CORP_AUTHORIZE . "?{$strApiParams}";

        return $url;
    }

    //---------------------------------------------------------------------------------------------------
    //微信 API 方法

    public static function getSuiteAccessToken($suiteID, $suiteSecret, $ticket)
    {
        $post = [
            'suite_id' => $suiteID,
            'suite_secret' => $suiteSecret,
            'suite_ticket' => $ticket,
        ];

        return parent::callApiPostJson(self::API_URL_GET_SUITE_ACCESS_TOKEN, $post);
    }

    /*
    * 获取预授权码pre_auth_code
    * 返回值:
    *       pre_auth_code : 预授权码。长度为64至512个字节
    *       expires_in : 有效期，为20分钟
    */
    public function createPreAuthCode()
    {
        $get = [
            'suite_access_token' => $this->suiteAccessToken
        ];

        $post = [
            'suite_id' => $this->suiteID,
        ];

        return parent::callApiPostJson(self::API_URL_CREATE_PRE_AUTH_CODE, $post, $get);
    }

    /*
    * 设置授权配置
    * 参数:
    *       $preAuthCode : 由微信第三方授权返回（URL回调后 或 事件通知）
    *       $suiteAppID :  允许进行授权的应用id，如1、2、3， 不填或者填空数组都表示允许授权套件内所有应用
    *       $suiteAuthType :  授权类型：0 正式授权， 1 测试授权， 默认值为0
    * 返回值:
    *       {
    *           "errcode": 0,
    *           "errmsg": "ok"
    *       }
    */
    public function setSessionInfo($preAuthCode, $suiteAppID = [], $suiteAuthType = 0)
    {
        $get = [
            'suite_access_token' => $this->suiteAccessToken
        ];

        $post = [
            'pre_auth_code' => $preAuthCode,
            'session_info' => [
                'appid' => $suiteAppID,
                'auth_type' => $suiteAuthType
            ],
        ];

        return parent::callApiPostJson(self::API_URL_SET_SESSION_INFO, $post, $get);
    }

    /*
    * 获取企业号的永久授权码
    * 参数:
    *       $authCode : 临时授权码，会在授权成功时附加在redirect_uri中跳转回应用提供商网站。长度为64至512个字节
    * 返回值:
    *       {
    *           access_token : 授权方（企业）access_token
    *           expires_in : 	授权方（企业）access_token超时时间
    *           permanent_code : 企业号永久授权码。长度为64至512个字节
    *           ......其他授权企业的信息
    *       }
    */
    public function getPermanentCode($authCode)
    {
        $get = [
            'suite_access_token' => $this->suiteAccessToken
        ];

        $post = [
            'suite_id' => $this->suiteID,
            'auth_code' => $authCode,
        ];

        return parent::callApiPostJson(self::API_URL_GET_PERMANENT_CODE, $post, $get);
    }


    /*
    * 获取企业号的授权信息
    * 参数:
    *       $authCorpID : 授权方corpid
    *       $permanentCode : 永久授权码，通过get_permanent_code获取
    * 返回值:
    *       {
    *           ......授权企业的信息
    *       }
    */
    public function getAuthInfo($authCorpID, $permanentCode)
    {
        $get = [
            'suite_access_token' => $this->suiteAccessToken
        ];

        $post = [
            'suite_id' => $this->suiteID,
            'auth_corpid' => $authCorpID,
            'permanent_code' => $permanentCode,
        ];

        return parent::callApiPostJson(self::API_URL_GET_AUTH_INFO, $post, $get);
    }

    /*
    * 获取企业号access_token
    * 参数:
    *       $authCorpID : 授权方corpid
    *       $permanentCode : 永久授权码，通过get_permanent_code获取
    * 返回值:
    *       {
    *           access_token : 授权方（企业）access_token
    *           expires_in : 授权方（企业）access_token超时时间
    *       }
    */
    public function getCorpToken($authCorpID, $permanentCode)
    {
        $get = [
            'suite_access_token' => $this->suiteAccessToken
        ];

        $post = [
            'suite_id' => $this->suiteID,
            'auth_corpid' => $authCorpID,
            'permanent_code' => $permanentCode,
        ];

        return parent::callApiPostJson(self::API_URL_GET_CORP_TOKEN, $post, $get);
    }
}