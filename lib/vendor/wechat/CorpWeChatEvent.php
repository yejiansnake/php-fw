<?php
/**
 * 微信企业号应用开发者
 */

namespace app\lib\vendor\wechat;

class CorpWeChatEvent extends BaseWeChatEvent
{
    public function getAuthContent(array $params)
    {
        if (empty($params['echostr']))
        {
            return null;
        }

        return parent::decryptMsg([
            'appID' => $this->appID,
            'token' => $this->token,
            'aesKey' => $this->aesKey,
            'sign' => $params['msg_signature'],
            'timestamp' => $params['timestamp'],
            'nonce' => $params['nonce'],
            'data' => $params['echostr'],
        ]);
    }
}