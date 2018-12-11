<?php

namespace app\lib\bl\wechat\login;

use app\lib\bl\LogMgr;
use Yii;
use yii\web\BadRequestHttpException;
use app\lib\common\HttpHelper;
use app\lib\bl\CachedMgr;
use app\lib\bl\wechat\CorpWeChatHelper;
use yii\web\UnauthorizedHttpException;

class CorpWeChatWebLogin  extends BaseLogin
{
    protected static $configKey = 'corpWeChat';
    protected static $cacheKeyPrefix = 'CWCWL';

    //获取微信验证跳转URL
    public static function handlePre($authResRoute = 'wechat/corp-web-auth-res')
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['url']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $config = self::getConfig();

        $key = self::createPreCacheKey();
        CachedMgr::set($key, [
            'url' => $params['url'],
        ], self::$expireTime);

        return ['data' => [
            'appId' => $config['corpID'],
            'agentId' => $config['agentID'],
            'redirectUri' => "{$config['webAuthHost']}/{$authResRoute}",
            'state' => $key,
        ]];
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
            throw new BadRequestHttpException('state invalid');
        }

        $key = self::createLoginCacheKey();
        CachedMgr::set($key, [
            'code' => $params['code'],
        ], self::$expireTime);

        $redirectUrl = HttpHelper::wrapUrl("{$info['url']}", [
            'key' => $key,
        ]);

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

        $info = CachedMgr::get($key, true);

        if (empty($info))
        {
            throw new UnauthorizedHttpException('key invalid');
        }

        $corpChatWeb = CorpWeChatHelper::createWeb();

        $userInfo = $corpChatWeb->getUserInfo($info['code']);

        if (!empty($userInfo['errcode']))
        {
            LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR,
                "get user info invalid, errcode:{$userInfo['errcode']}, errmsg:{$userInfo['errmsg']}");

            throw new BadRequestHttpException('get user info invalid');
        }

        $corpChat = CorpWeChatHelper::create();

        return $corpChat->getUser($userInfo['UserId']);
    }
}