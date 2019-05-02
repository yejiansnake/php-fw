<?php
/**
 * 微信功能接口
 * 流程：
 * 1.前端 ---> api/xxx/auth/pre 获取跳转地址（传入验证成功后的跳回地址）
 * 2.前端 跳转微信验证地址
 * 3./wechat/{微信公众号子系统}-auth-res ---> 跳转前端 sourceURL 并提供 key 进行验证登录
 * 4.前端使用 key => api/xxx/auth/login 进行登录
 */

namespace app\controllers;

use Yii;
use app\lib\bl\wechat\login\WeChatLogin;
use app\lib\bl\wechat\login\CorpWeChatLogin;
use app\lib\bl\wechat\login\CorpWeChatWebLogin;
use app\lib\bl\wechat\login\CompanyCorpWeChatLogin;
use app\lib\bl\wechat\login\CompanyCorpWeChatWebLogin;

class WechatController extends BaseController
{
    //MP:微信公众号
    public function actionMpAuthRes()
    {
        return $this->redirect(WeChatLogin::handleAuthRes());
    }

    //Corp:企业微信
    public function actionCorpAuthRes()
    {
        return $this->redirect(CorpWeChatLogin::handleAuthRes());
    }

    //CorpWeb:企业微信WEB扫码(后台)
    public function actionCorpWebAuthRes()
    {
        return $this->redirect(CorpWeChatWebLogin::handleAuthRes());
    }

    //CompanyCorp:客户企业微信扫码
    public function actionCompanyCorpAuthRes()
    {
        return $this->redirect(CompanyCorpWeChatLogin::handleAuthRes());
    }

    //CompanyCorpWeb:客户企业微信WEB扫码(后台)
    public function actionCompanyCorpWebAuthRes()
    {
        return $this->redirect(CompanyCorpWeChatWebLogin::handleAuthRes());
    }
}