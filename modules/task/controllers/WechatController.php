<?php
/**
 * 微信相关任务
 */

namespace app\modules\task\controllers;

use Yii;
use app\lib\bl\wechat\WeChatHelper;

class WechatController extends BaseController
{
    public function actionTest()
    {
        return self::EXIT_CODE_NORMAL;
    }

    public function actionUpdateToken()
    {
        self::updateAccessToken();

        self::updateJsTicket();
    }

    public static function updateAccessToken()
    {
        $res = WeChatHelper::createAccessToken();

        if (empty($res))
        {
            return;
        }

        WeChatHelper::saveAccessToken($res['access_token'], $res['expires_in']);
    }

    public static function updateJsTicket()
    {
        $res = WeChatHelper::createJsTicket();

        if (empty($res))
        {
            return;
        }

        WeChatHelper::saveJsTicket($res['ticket'], $res['expires_in']);
    }
}

