<?php
/**
 * 微信第三方平台事件处理
 */

namespace app\lib\bl\wechat\event;

use Yii;
use app\lib\common\controller\EventBaseController;
use app\lib\bl\LogMgr;
use app\lib\bl\wechat\WeChatCompHelper;

class WechatCompEventBaseController extends EventBaseController
{
    //---------------------------------------------------------------------------

    private static $eventMap = [
        'component_verify_ticket' => 'handleComponentVerifyTicket',     //推送的第三方平台授权ticket
        'authorized' => 'handleAuthorized',             //授权成功通知
        'updateauthorized' => 'handleUpdateAuthorized', //授权更新通知
        'unauthorized' => 'handleUnauthorized',         //取消授权通知
    ];

    //---------------------------------------------------------------------------

    protected function handleComponentVerifyTicket($msg)
    {
        WeChatCompHelper::saveCompTicket($msg['ComponentVerifyTicket']);
    }

    protected function handleAuthorized($msg)
    {
        return WeChatCompHelper::saveClientAuth($msg['AuthorizationCode']);
    }

    protected function handleUpdateAuthorized($msg)
    {
        return WeChatCompHelper::saveClientAuth($msg['AuthorizationCode']);
    }

    protected function handleUnauthorized($msg)
    {
        return WeChatCompHelper::cancelClientAuth($msg['AuthorizerAppid']);
    }

    //---------------------------------------------------------------------------

    const DEFAULT_RETURN_MSG_ERROR = '';
    const DEFAULT_RETURN_MSG_SUCCESS = 'success';

    public function actionReceive()
    {
        $data = Yii::$app->request->rawBody;
        $params = Yii::$app->request->queryParams;
        $strParams = json_encode($params, JSON_UNESCAPED_UNICODE);

        LogMgr::event(__METHOD__, LogMgr::LEVEL_DEBUG,
            "params: {$strParams}, data:{$data}");

        if (empty($params['msg_signature'])
            || empty($params['signature'])
            || empty($params['timestamp'])
            || empty($params['nonce']))
        {
            LogMgr::event(__METHOD__, LogMgr::LEVEL_ERROR,
                "params failed, params:{$strParams}");

            return self::DEFAULT_RETURN_MSG_ERROR;
        }

        $event = WeChatCompHelper::createEvent();

        ///////////////////////////////////////////////////////////////////////////////
        //消息校验

        if (!$event->checkSignature($params))
        {
            LogMgr::event(__METHOD__, LogMgr::LEVEL_ERROR,
                "signature check failed, params:{$strParams}");

            return self::DEFAULT_RETURN_MSG_ERROR;
        }

        ///////////////////////////////////////////////////////////////////////////////
        //事件处理

        try
        {
            $msg = $event->getMsgContent([
                'sign' => $params['msg_signature'],
                'timestamp' =>$params['timestamp'],
                'nonce' =>$params['nonce'],
                'data' => $data]);

            if (!empty($msg))
            {
                if (LogMgr::isLog('event', LogMgr::LEVEL_DEBUG))
                {
                    LogMgr::event(__METHOD__, LogMgr::LEVEL_DEBUG,
                        "msg content:" . json_encode($msg, JSON_UNESCAPED_UNICODE));
                }

                $res = self::handleMsg($msg);

                if (!empty($res))
                {
                    $retMsg = $event->getReturnMsg($msg, $res);

                    if (!empty($retMsg))
                    {
                        return $retMsg;
                    }
                }
            }
            else
            {
                LogMgr::event(__METHOD__, LogMgr::LEVEL_ERROR,
                    "get msg content failed, params: {$strParams}, data:{$data}");
            }
        }
        catch (\Exception $ex)
        {
            $trace = json_encode(['file' => $ex->getFile(), 'line' => $ex->getLine()], JSON_UNESCAPED_UNICODE);
            LogMgr::event(__METHOD__, LogMgr::LEVEL_ERROR,
                "catch msg: {$ex->getMessage()} trace:{$trace}" .
                "\n params:" . json_encode(Yii::$app->request->queryParams) .
                "\n raw:" . Yii::$app->request->rawBody);

            return self::DEFAULT_RETURN_MSG_ERROR;
        }

        return self::DEFAULT_RETURN_MSG_SUCCESS;
    }

    private function handleMsg($msg)
    {
        LogMgr::event(__METHOD__, LogMgr::LEVEL_DEBUG,
            "handle msg type: {$msg['InfoType']}");

        $type = $msg['InfoType'];

        if (array_key_exists($type, self::$eventMap))
        {
            $callbackFunc = self::$eventMap[$type];
            return $this->$callbackFunc($msg);
        }

        return null;
    }
}