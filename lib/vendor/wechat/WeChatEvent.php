<?php

namespace app\lib\vendor\wechat;

class WeChatEvent extends BaseWeChat
{
    //---------------------------------------------------------------------------------------------------
    //加密类型

    const ENCRYPT_TYPE_NONE = 0;
    const ENCRYPT_TYPE_BOTH = 1;
    const ENCRYPT_TYPE_SAFE = 2;

    //---------------------------------------------------------------------------------------------------
    //回复的消息类型

    const RET_MSG_TEXT = 1;
    const RET_MSG_IMAGE = 2;
    const RET_MSG_VOICE = 3;
    const RET_MSG_VIDEO = 4;
    const RET_MSG_MUSIC = 5;
    const RET_MSG_ARTICLE = 6;

    private static $RET_MSG_MAP = [
        self::RET_MSG_TEXT => 'text',
        self::RET_MSG_IMAGE => 'image',
        self::RET_MSG_VOICE => 'voice',
        self::RET_MSG_VIDEO => 'video',
        self::RET_MSG_MUSIC => 'music',
        self::RET_MSG_ARTICLE => 'news',
    ];

    //---------------------------------------------------------------------------------------------------
    //参数名称

    protected $id = '';
    protected $appID = '';
    protected $token = '';
    protected $aesKey = '';
    protected $encryptType = 0;

    //---------------------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['appID'])
            || empty($options['token'])
            || empty($options['aesKey'])
            || empty($options['encryptType']))
        {
            throw new \InvalidArgumentException();
        }

        $this->id = empty($options['id']) ? null : $options['id'];
        $this->appID = $options['appID'];
        $this->token = $options['token'];
        $this->aesKey = $options['aesKey'];
        $this->encryptType = $options['encryptType'];
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

        if ($this->encryptType == self::ENCRYPT_TYPE_SAFE)
        {
            $msg = self::getDecryptMsg($params);
        }
        else
        {
            $msg = self::xmlToArray($params['data']);
        }

        return $msg;
    }

    public function getReturnMsg($msg, array $params)
    {
        $data = self::warpRetMsg($msg, $params);

        if (empty($data))
        {
            return null;
        }

        if ($this->encryptType == self::ENCRYPT_TYPE_SAFE)
        {
            return self::getEncryptMsg($data);
        }

        return $data;
    }

    private function warpRetMsg($msg, array $params)
    {
        if (empty($msg) || empty($params))
        {
            return null;
        }

        if (empty($params['type']))
        {
            return null;
        }

        if (!array_key_exists($params['type'], self::$RET_MSG_MAP))
        {
            return null;
        }

        $temp = $this->warpRetMsgContent($params);

        if (empty($temp))
        {
            return null;
        }

        $msgType = self::$RET_MSG_MAP[$params['type']];
        $time = time();

        return"<xml><ToUserName><![CDATA[{$msg['FromUserName']}]]></ToUserName>
<FromUserName><![CDATA[{$this->id}]]></FromUserName>
<CreateTime>{$time}</CreateTime>
<MsgType><![CDATA[{$msgType}]]></MsgType>{$temp}</xml>";
    }

    private function warpRetMsgContent(array $params)
    {
        $type = $params['type'];
        $content = $params['content'];

        switch ($type)
        {
            case self::RET_MSG_TEXT:
            {
                return self::warpRetMsgText($content);
            }
            break;
            case self::RET_MSG_IMAGE:
            {
                return self::warpRetMsgImage($content);
            }
            break;
            case self::RET_MSG_VOICE:
            {
                return self::warpRetMsgVoice($content);
            }
            break;
            case self::RET_MSG_VIDEO:
            {
                return self::warpRetMsgVideo($content);
            }
            break;
            case self::RET_MSG_MUSIC:
            {
                return self::warpRetMsgMusic($content);
            }
            break;
            case self::RET_MSG_ARTICLE:
            {
                return self::warpRetMsgArticle($content);
            }
            break;
        }

        return null;
    }

    private function warpRetMsgText($content)
    {
        return "<Content><![CDATA[{$content}]]></Content>";
    }

    private function warpRetMsgImage($mediaID)
    {
        if (empty($mediaID))
        {
            return null;
        }

        return "<Image><MediaId><![CDATA[{$mediaID}]]></MediaId></Image>";
    }

    private function warpRetMsgVoice($mediaID)
    {
        if (empty($mediaID))
        {
            return null;
        }

        return "<Voice><MediaId><![CDATA[{$mediaID}]]></MediaId></Voice>";
    }

    private function warpRetMsgVideo(array $params)
    {
        if (empty($params))
        {
            return null;
        }

        if (empty($params['mediaID']) || empty($params['title']) || empty($params['desc']))
        {
            return null;
        }

        $titleMsg = '';
        if (!empty($params['title']))
        {
            $titleMsg = "<Title><![CDATA[{$params['title']}]]></Title>";
        }

        $descMsg = '';
        if (!empty($params['desc']))
        {
            $descMsg = "<Description><![CDATA[{$params['desc']}]]></Description>";
        }

        return "<Video><MediaId><![CDATA[{$params['mediaID']}]]></MediaId>{$titleMsg}{$descMsg}</Video>";
    }

    private function warpRetMsgMusic(array $params)
    {
        if (empty($params))
        {
            return null;
        }

        if (empty($params['mediaID'])
            || empty($params['title'])
            || empty($params['desc'])
            || empty($params['url'])
            || empty($params['hqUrl']))
        {
            return null;
        }

        $titleMsg = '';
        if (!empty($params['title']))
        {
            $titleMsg = "<Title><![CDATA[{$params['title']}]]></Title>";
        }

        $descMsg = '';
        if (!empty($params['desc']))
        {
            $descMsg = "<Description><![CDATA[{$params['desc']}]]></Description>";
        }

        $urlMsg = '';
        if (!empty($params['url']))
        {
            $urlMsg = "<MusicUrl><![CDATA[{$params['url']}]]></MusicUrl>";
        }

        $hqUrlMsg = '';
        if (!empty($params['hqUrl']))
        {
            $hqUrlMsg = "<HQMusicUrl><![CDATA[{$params['hqUrl']}]]></HQMusicUrl>";
        }

        return "<Music><ThumbMediaId><![CDATA[{$params['mediaID']}]]></ThumbMediaId>
{$titleMsg}{$descMsg}{$urlMsg}{$hqUrlMsg}</Music>";
    }

    private function warpRetMsgArticle($params)
    {
        if (empty($params))
        {
            return null;
        }

        $count = count($params);

        $msg = "<Articles><ArticleCount>{$count}</ArticleCount><Articles>";

        foreach ($params as $item)
        {
            $titleMsg = '';
            if (!empty($item['title']))
            {
                $titleMsg = "<Title><![CDATA[{$item['title']}]]></Title>";
            }

            $descMsg = '';
            if (!empty($params['desc']))
            {
                $descMsg = "<Description><![CDATA[{$item['desc']}]]></Description>";
            }

            $picUrlMsg = '';
            if (!empty($item['picUrl']))
            {
                $picUrlMsg = "<HQMusicUrl><![CDATA[{$item['picUrl']}]]></HQMusicUrl>";
            }

            $urlMsg = '';
            if (!empty($item['url']))
            {
                $urlMsg = "<MusicUrl><![CDATA[{$item['url']}]]></MusicUrl>";
            }

            $msg .= "<item>{$titleMsg}{$descMsg}{$picUrlMsg}{$urlMsg}</item>";
        }

        return $msg .'</Articles>';
    }

    //---------------------------------------------------------------------------------------------------

    private function getDecryptMsg(array $params)
    {
        if (empty($params['sign'])
            || empty($params['timestamp'])
            || empty($params['nonce'])
            || empty($params['data']))
        {
            throw new \Exception('params invalid');
        }

        $msg = self::xmlToArray($params['data']);

        if (empty($msg['Encrypt']))
        {
            throw new \Exception('msg format invalid');
        }

        //LogMgr::addEventLog(__METHOD__, LogMgr::LEVEL_INFO, "decryptMsg start");

        $content = parent::decryptMsg([
            'appID' => $this->appID,
            'token' => $this->token,
            'aesKey' => $this->aesKey,
            'sign' => $params['sign'],
            'timestamp' => $params['timestamp'],
            'nonce' => $params['nonce'],
            'data' => $msg['Encrypt'],
        ]);

        //LogMgr::addEventLog(__METHOD__, LogMgr::LEVEL_INFO, "decryptMsg end");

        if (empty($content))
        {
            throw new \Exception("decrypt msg failed");
        }

        return self::xmlToArray($content);
    }

    private function getEncryptMsg($data)
    {
         return parent::encryptMsg([
            'appID' => $this->appID,
            'token' => $this->token,
            'aesKey' => $this->aesKey,
            'data' => $data,
        ]);
    }
}