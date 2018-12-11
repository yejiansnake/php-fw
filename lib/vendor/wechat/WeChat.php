<?php
/**
 * 微信功能类
 */

namespace app\lib\vendor\wechat;

class WeChat extends BaseWeChat
{
    private $appID = '';
    private $accessToken = '';

    //-----------------------------------------------------------------------------------------

    //获取公众号 ACCESS_TOKEN
    const API_URL_GET_ACCESS_TOKEN = 'https://api.weixin.qq.com/cgi-bin/token';

    //获取公众号 JS_API_TICKET
    const API_URL_GET_JS_API_TICKET = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket';

    //获取用户基本信息(UnionID机制)
    const API_URL_USER_INFO = 'https://api.weixin.qq.com/cgi-bin/user/info';

    //自定义菜单创建接口
    const API_URL_MENU_CREATE = 'https://api.weixin.qq.com/cgi-bin/menu/create';

    //获取临时素材
    const API_URL_MEDIA_GET = 'https://api.weixin.qq.com/cgi-bin/media/get';

    //获取临时素材 - 视频文件, 返回信息： "video_url":DOWN_URL
    const API_URL_MEDIA_GET_VIDEO = 'http://api.weixin.qq.com/cgi-bin/media/get';

    //获取临时素材 - 高清语音素材获取接口 ?access_token=ACCESS_TOKEN&media_id=MEDIA_ID
    const API_URL_MEDIA_GET_JSSDK = 'https://api.weixin.qq.com/cgi-bin/media/get/jssdk';

    //-----------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['appID'])
            || empty($options['access_token']))
        {
            throw new \InvalidArgumentException();
        }

        $this->appID = $options['appID'];
        $this->accessToken = $options['access_token'];
    }

    public static function getAccessToken($appID, $appSecret)
    {
        $params = [
            'grant_type' => 'client_credential',
            'appid' => $appID,
            'secret' => $appSecret,
        ];

        return self::callApi(self::API_URL_GET_ACCESS_TOKEN, $params);
    }

    public static function getJsApiTicket($accessToken)
    {
        if (empty($accessToken))
        {
            return null;
        }

        $params = [
            'type' => 'jsapi',
            'access_token' => $accessToken,
        ];

        return self::callApi(self::API_URL_GET_JS_API_TICKET, $params);
    }

    public function getUserInfo($openid)
    {
        $params = [
            'access_token' => $this->accessToken,
            'openid' => $openid,
        ];

        return self::callApi(self::API_URL_USER_INFO, $params);
    }

    public function createMenu($menuObj)
    {
        $params = [
            'access_token' => $this->accessToken,
        ];

        return self::callApiPostJson(self::API_URL_MENU_CREATE, $menuObj, $params);
    }

    public function getMedia($mediaID)
    {
        $params = [
            'access_token' => $this->accessToken,
            'media_id' => $mediaID
        ];

        return self::callApiRaw(self::API_URL_MEDIA_GET, $params, null, true);
    }

    public function getMediaVideo($mediaID)
    {
        $params = [
            'access_token' => $this->accessToken,
            'media_id' => $mediaID
        ];

        return self::callApi(self::API_URL_MEDIA_GET_VIDEO, $params, null, null);
    }

    public function getMediaJSSDK($mediaID)
    {
        $params = [
            'access_token' => $this->accessToken,
            'media_id' => $mediaID
        ];

        return self::callApiRaw(self::API_URL_MEDIA_GET_JSSDK, $params, null, true);
    }
}