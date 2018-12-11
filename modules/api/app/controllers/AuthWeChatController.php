<?php

namespace app\modules\api\app\controllers;


use Yii;
use yii\web\BadRequestHttpException;
use app\lib\bl\Enum;
use app\lib\bl\AuthTrait;
use app\lib\bl\wechat\WeChatHelper;
use app\lib\bl\wechat\login\WeChatLogin;
use app\lib\bl\session\CurUserSession;
use app\models\client\SysPropsModel;
use app\models\client\AppUserModel;

abstract class AuthWeChatController extends BaseController
{
    use AuthTrait;

    public function actionPre()
    {
        return WeChatLogin::handlePre();
    }

    //验证
    public function actionLogin()
    {
        if (CurUserSession::exist())
        {
            return CurUserSession::get();
        }
        $weChatUser = WeChatLogin::handleLogin();
        $userInfo = AppUserModel::saveOneFromWxChat($weChatUser);

        $this->resetCurUser($userInfo);

        return $userInfo;
    }

    //js sdk token 与 初始化信息
    public function actionBase()
    {
        $params = Yii::$app->request->queryParams;

        if (empty($params['url']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $url = urldecode($params['url']);
        $jsSign = WeChatHelper::getJsSign($url);

        $res = [
            'data' => [
                'wxJs' => $jsSign,
            ],
        ];

        //微信信息
        $wxShareInfo = SysPropsModel::getOne([
            '@select' => ['value7'],
            'type' => Enum::CLIENT_PM_TYPE_BASE,
            'id' => Enum::CLIENT_PM_TYPE_BASE_ID_WECHAT_SHARE]);

        $res['data']['wx'] = empty($wxShareInfo) ? [] : $wxShareInfo->toArray(['value7'])['value7'];

        return $res;
    }
}