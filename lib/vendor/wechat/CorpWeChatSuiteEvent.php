<?php
/**
 * 微信企业号应用开发者
 */

namespace app\lib\vendor\wechat;

class CorpWeChatSuiteEvent extends BaseWeChat
{
    //---------------------------------------------------------------------------------------------------
    //参数名称

    protected $suiteID = '';
    protected $token = '';
    protected $aesKey = '';

    //---------------------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['suiteID'])
            || empty($options['token'])
            || empty($options['aesKey']))
        {
            throw new \InvalidArgumentException();
        }

        $this->suiteID = $options['suiteID'];
        $this->token = $options['token'];
        $this->aesKey = $options['aesKey'];
    }

    public function checkSignature(array $params)
    {
        $tmpArr = array($this->token, $params['timestamp'], $params['nonce']);

        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if($tmpStr != $params["signature"])
        {
            return false;
        }

        return true;
    }

    public function getAuthContent(array $params)
    {
        if (empty($params['echostr']))
        {
            return null;
        }

        return $params['echostr'];
    }

    public function getMsgContent(array $params)
    {
        $msg = '';

        $msg = self::getDecryptMsg($params);

        if ($msg['SuiteId'] != $this->suiteID)
        {
            return null;
        }

        return $msg;
    }

    public function getDecryptMsg(array $params)
    {
        if (empty($params['sign'])
            || empty($params['timestamp'])
            || empty($params['nonce'])
            || empty($params['data']))
        {
            throw new \Exception('params invalid');
        }

        $msg = self::xmlToArray($params['data']);

        if (empty($msg['ToUserName']) || empty($msg['Encrypt']))
        {
            throw new \Exception('msg format invalid');
        }

        if ($msg['ToUserName'] != $this->suiteID)
        {
            throw new \Exception("sys suiteID:{$this->suiteID} != msg suiteID:{$msg['ToUserName']}");
        }

        $content = parent::decryptMsg([
            'appID' => $this->suiteID,
            'token' => $this->token,
            'aesKey' => $this->aesKey,
            'sign' => $params['sign'],
            'timestamp' => $params['timestamp'],
            'nonce' => $params['nonce'],
            'data' => $msg['Encrypt'],
        ]);

        if (empty($content))
        {
            throw new \Exception("decrypt msg failed");
        }

        return self::xmlToArray($content);
    }
}