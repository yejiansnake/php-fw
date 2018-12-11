<?php

namespace app\lib\bl\wechat\login;

use app\lib\bl\LogMgr;
use Yii;
use yii\web\BadRequestHttpException;
use app\lib\bl\wechat\WeChatHelper;
use app\lib\bl\CachedMgr;
use app\lib\vendor\wechat\WeChatWeb;
use yii\web\UnauthorizedHttpException;

class WeChatLogin extends BaseLogin
{
    protected static $configKey = 'weChat';
    protected static $cacheKeyPrefix = 'WCL';

    //获取微信验证跳转URL
    public static function handlePre($authResRoute = 'wechat/mp-auth-res')
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['url']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $config = self::getConfig();

        $host = $config['authHost'];
        $scopeType = empty($config['scope']) ? WeChatWeb::SCOPE_TYPE_SNSAPI_BASE : $config['scope'];

        $key = self::createPreCacheKey();
        CachedMgr::set($key, [
            'scopeType' => $scopeType,
            'url' => $params['url'],
        ], static::$expireTime);

//        $clientIP = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
//        LogMgr::common(__METHOD__, LogMgr::LEVEL_ERROR,
//            "create cache key: {$key}, client ip:{$clientIP}");

        $weChatWeb = WeChatHelper::createWeb();

        return [
            'url' => $weChatWeb->getAuthorizeUrl("{$host}/{$authResRoute}", $key, $scopeType),
        ];
    }

    //微信授权跳转回的URL（固定域名，微信安全域名要求）
    public static function handleAuthRes()
    {
        $params = Yii::$app->request->queryParams;

        if (empty($params['code'])
            || empty($params['state'])
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        $key = $params['state'];

        $info = CachedMgr::get($key, true);

        if (empty($info))
        {
            $clientIP = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
            LogMgr::debug(__METHOD__, LogMgr::LEVEL_ERROR,
                "state invalid, key:{$key}, client ip:{$clientIP}");

            throw new BadRequestHttpException('state invalid');
        }

        $key = self::createLoginCacheKey();
        CachedMgr::set($key, [
            'scopeType' => $info['scopeType'],
            'code' => $params['code'],
        ], static::$expireTime);

        $urlEncodeKey = urlencode($key);

        $redirectUrl = "{$info['url']}?key={$urlEncodeKey}";

        return $redirectUrl;
    }

    //验证
    public static function handleLogin()
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['key']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $key = $params['key'];
        $urlDecodeKey = urldecode($params['key']);

        $info = CachedMgr::get($urlDecodeKey, true);

        if (empty($info))
        {
//            $clientIP = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
//            LogMgr::debug(__METHOD__, LogMgr::LEVEL_ERROR,
//                "key invalid, key:{$key}, client ip:{$clientIP}");

            throw new UnauthorizedHttpException('key invalid');
        }

        $weChatWeb = WeChatHelper::createWeb();
        $accessTokenData = $weChatWeb->accessToken($info['code']);

        $res = ['openid' => $accessTokenData['openid']];

        if ($info['scopeType'] == WeChatWeb::SCOPE_TYPE_SNSAPI_USERINFO)
        {
            $res = $weChatWeb->snsUserInfo($accessTokenData['access_token'], $accessTokenData['openid']);
        }

        return $res;
    }
}