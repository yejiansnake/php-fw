<?php
/**
 * 企业微信相关任务
 */

namespace app\modules\task\controllers;

use Yii;
use app\lib\bl\wechat\CorpWeChatHelper;

class CorpWechatController extends BaseController
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
        $res = CorpWeChatHelper::createAccessToken();

        if (empty($res))
        {
            return;
        }

        CorpWeChatHelper::saveAccessToken($res['access_token'], $res['expires_in']);
    }

    public static function updateJsTicket()
    {
        $res = CorpWeChatHelper::createJsTicket();

        if (empty($res))
        {
            return;
        }

        CorpWeChatHelper::saveJsTicket($res['ticket'], $res['expires_in']);
    }
}