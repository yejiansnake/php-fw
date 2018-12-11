<?php
/**
 * 微信事件处理
 */

namespace app\lib\bl\wechat\event;

use Yii;
use app\lib\common\controller\EventBaseController;
use app\lib\bl\LogMgr;
use app\lib\bl\wechat\WeChatHelper;

class WechatEventBaseController extends EventBaseController
{
    //消息:文本
    protected function handleMsgText($msg)
    {
        return null;
    }

    //消息:图片
    protected function handleMsgImage($msg)
    {
        return null;
    }

    //消息:语音
    protected function handleMsgVoice($msg)
    {
        return null;
    }

    //消息:视频
    protected function handleMsgVideo($msg)
    {
        return null;
    }

    //消息:小视频
    protected function handleMsgShortVideo($msg)
    {
        return null;
    }

    //消息:地理位置
    protected function handleMsgLocation($msg)
    {
        return null;
    }

    //消息:链接
    protected function handleMsgLink($msg)
    {
        return null;
    }

    //事件:订阅:扫描带参数二维码事件用户未关注 EventKey Ticket
    protected function handleMsgEventSubscribe($msg)
    {
        return null;
    }

    //事件:取消订阅
    protected function handleMsgEventUnsubscribe($msg)
    {
        return null;
    }

    //事件:扫描带参数二维码事件用户已关注 EventKey Ticket
    protected function handleMsgEventScan($msg)
    {
        return null;
    }

    //事件:上报地理位置事件
    protected function handleMsgEventLocation($msg)
    {
        return null;
    }

    //事件:自定义菜单事件
    protected function handleMsgEventClick($msg)
    {
        return null;
    }

    //---------------------------------------------------------------------------

    const DEFAULT_RETURN_MSG_ERROR = '';
    const DEFAULT_RETURN_MSG_SUCCESS = 'success';

    public function actionReceive()
    {
        $params = Yii::$app->request->queryParams;
        $strParams = json_encode($params, JSON_UNESCAPED_UNICODE);

        LogMgr::event(__METHOD__, LogMgr::LEVEL_DEBUG,
            "params: {$strParams}");

        if (empty($params['signature'])
            || empty($params['timestamp'])
            || empty($params['nonce']))
        {
            LogMgr::event(__METHOD__, LogMgr::LEVEL_ERROR,
                "params failed, params:{$strParams}");

            return self::DEFAULT_RETURN_MSG_ERROR;
        }

        $event = WeChatHelper::createEvent();

        ///////////////////////////////////////////////////////////////////////////////
        //消息校验

        if (!$event->checkSignature($params))
        {
            LogMgr::event(__METHOD__, LogMgr::LEVEL_ERROR,
                "signature check failed, params:{$strParams}");

            return self::DEFAULT_RETURN_MSG_ERROR;
        }

        ///////////////////////////////////////////////////////////////////////////////
        //事件授权验证

        $authContent = $event->getAuthContent($params);

        if (!empty($authContent))
        {
            LogMgr::event(__METHOD__, LogMgr::LEVEL_ERROR, 'return content');

            return $authContent;
        }

        ///////////////////////////////////////////////////////////////////////////////
        //事件处理

        $data = Yii::$app->request->rawBody;

        LogMgr::event(__METHOD__, LogMgr::LEVEL_DEBUG, "raw:" . $data);

        try
        {
            $msg = $event->getMsgContent([
                'sign' => empty($params['msg_signature']) ? '' : $params['msg_signature'],
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
            "handle msg type: {$msg['MsgType']}");

        switch ($msg['MsgType'])
        {
            case 'text':
            {
                return $this->handleMsgText($msg);
            }
            break;
            case 'image':
            {
                return $this->handleMsgImage($msg);
            }
            break;
            case 'voice':
            {
                return $this->handleMsgVoice($msg);
            }
            break;
            case 'video':
            {
                return $this->handleMsgVideo($msg);
            }
            break;
            case 'shortvideo':
            {
                return $this->handleMsgShortVideo($msg);
            }
            break;
            case 'location':
            {
                return $this->handleMsgLocation($msg);
            }
            break;
            case 'link':
            {
                return $this->handleMsgLink($msg);
            }
            break;
            case 'event':
            {
                self::handleMsgEvent($msg);
            }
            break;
        }

        return null;
    }

    private function handleMsgEvent($msg)
    {
        switch ($msg['Event'])
        {
            case 'subscribe':
            {
                $this->handleMsgEventSubscribe($msg);
            }
            break;
            case 'unsubscribe':
            {
                $this->handleMsgEventUnsubscribe($msg);
            }
            break;
            case 'SCAN':
            {
                $this->handleMsgEventLocation($msg);
            }
            break;
            case 'LOCATION':
            {
                $this->handleMsgEventLocation($msg);
            }
            break;
            case 'CLICK':
            {
                $this->handleMsgEventClick($msg);
            }
            break;
        }

        return null;
    }
}