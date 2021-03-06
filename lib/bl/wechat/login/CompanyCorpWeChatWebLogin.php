<?php

namespace app\lib\bl\wechat\login;

use app\lib\bl\LogMgr;
use app\lib\bl\wechat\CompanyCorpWeChatHelper;
use app\lib\common\HttpHelper;
use Yii;
use yii\web\BadRequestHttpException;
use app\lib\bl\CachedMgr;
use yii\web\UnauthorizedHttpException;

class CompanyCorpWeChatWebLogin extends CompanyBaseLogin
{
    protected static $cacheKeyPrefix = 'CCWCWL';

    public static function handlePre($companyID, $authResRoute = 'wechat/company-corp-web-auth-res')
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['url']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $config = self::getConfig($companyID);

        $key = self::createPreCacheKey();
        CachedMgr::set($key, [
            'url' => $params['url'],
        ], self::$expireTime);

        $httpProtocol = HttpHelper::getProtocol();

        return ['data' => [
            'appId' => $config['app_id'],
            'agentId' => $config['agent_id'],
            'redirectUri' => "{$httpProtocol}//{$_SERVER['HTTP_HOST']}/{$authResRoute}",
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
    public static function handleLogin($companyID)
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

        $corpChatWeb = CompanyCorpWeChatHelper::createWeb($companyID);

        $userInfo = $corpChatWeb->getUserInfo($info['code']);

        if (!empty($userInfo['errcode']))
        {
            LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR,
                "get user info invalid, errcode:{$userInfo['errcode']}, errmsg:{$userInfo['errmsg']}");

            throw new BadRequestHttpException('get user info invalid');
        }

        $corpChat = CompanyCorpWeChatHelper::create($companyID);

        return $corpChat->getUser($userInfo['UserId']);
    }
}