<?php
/**
 * 基类
 */

namespace app\lib\vendor\wechat;

use app\lib\bl\LogMgr;
use app\lib\common\WebRequest;
use app\lib\vendor\wechat\base\WXBizMsgCrypt;

abstract class BaseWeChat
{
    public function __construct(array $options = [])
    {
        $this->init($options);
    }

    protected function init(array $options)
    {

    }

    //消息加密
    protected static function encryptMsg(array $params)
    {
        return WXBizMsgCrypt::encryptMsg($params);
    }

    //消息解密
    protected static function decryptMsg(array $params)
    {
        return WXBizMsgCrypt::decryptMsg($params);
    }

    /**
     * @param $apiUrl
     * @param $post
     * @param array $get
     * @return mixed
     * @throws \Exception
     */
    protected static function callApiPostJson($apiUrl, $post, array $get = [])
    {
        return self::callApi($apiUrl, $get, json_encode($post, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param $apiUrl
     * @param $post
     * @param array $get
     * @return mixed
     * @throws \Exception
     */
    protected static function callApiPost($apiUrl, $post, array $get = [])
    {
        return self::callApi($apiUrl, $get, $post);
    }

    /**
     * @param $apiUrl
     * @param array $get
     * @param null $post
     * @param array $ssl
     * @return mixed
     * @throws \Exception
     */
    protected static function callApi($apiUrl, array $get = [], $post = null, $ssl = [])
    {
        $res = self::getResponse($apiUrl, [
            'get' => $get,
            'post' => $post,
            'ssl' => $ssl,
        ]);

       return $res['data'];
    }

    protected static function callApiRaw($apiUrl, array $get = [], $post = null, $ssl = null)
    {
        $params = [
            'raw' => true,
        ];

        if (!empty($get))
        {
            $params['get'] = $get;
        }

        if (!empty($post))
        {
            $params['post'] = $post;
        }

        if (isset($ssl))
        {
            $params['ssl'] = $ssl;
        }

        $res = self::getResponse($apiUrl, $params);

        $info = $res['info'];

        return [
            'type' => $info['content_type'],
            'length' => $info['download_content_length'],
            'data' => $res['data'],
        ];
    }

    private static function getResponse($url, array $params = [])
    {
        if (empty($url))
        {
            throw new \Exception('params invalid');
        }

        $params = empty($params) ? [] : $params;

        $webRes = WebRequest::getResponse($url, $params);
//        print_r($webRes);exit;
        if (empty($webRes['success']))
        {
            throw new \Exception("WebRequest failed url:[{$url}], errno:{$webRes['errno']}, error:{$webRes['error']}");
        }

        if ($webRes['info']['http_code'] != 200)
        {
            throw new \Exception("WebRequest failed, http code:{$webRes['code']}");
        }

        $data = [];
        $apiSuccess = true;

        if (isset($params['raw']))
        {
            //如果存在，则说明返回错误
            if (false !== strpos($webRes['data'], 'errcode'))
            {
                $data = json_decode($webRes['data'], true);
                $apiSuccess = false;
            }
            else
            {
                //获取不解析的元数据
                $data = $webRes['data'];
            }
        }
        else
        {
            $data = json_decode($webRes['data'], true);

            if (!empty($data['errcode']))
            {
                $apiSuccess = false;
            }
        }

        if (empty($apiSuccess))
        {
            self::addErrorLog($url, $params, $data, $webRes);
            throw new \Exception('call wechat api failed');
        }

        return [
            'info' => $webRes['info'],
            'data' => $data,
        ];
    }

    private static function addErrorLog($url, $params, $data, $webRes)
    {
        $error = "code=[{$data['errcode']}], msg=[{$data['errmsg']}]";
        $paramsInfo = json_encode($params, JSON_UNESCAPED_UNICODE);
        $webResInfo = json_encode($webRes, JSON_UNESCAPED_UNICODE);
        LogMgr::wechat(__METHOD__, LogMgr::LEVEL_ERROR,
            "call wechat api failed, url:{$url}, info:{$error},  params:{$paramsInfo}, web res:{$webResInfo}");
    }

    protected static function arrayToXml(array $params)
    {
        if (empty($params)) {
            return '';
        }

        $res = '';

        $res .= '<xml>';
        foreach ($params as $key => $value)
        {
            if (is_numeric($value))
            {
                $res .= "<{$key}>{$value}</{$key}>";
            }
            else
            {
                $res .= "<{$key}><![CDATA[{$value}]]></{$key}>";
            }
        }
        $res .= '</xml>';

        return $res;
    }

    protected static function xmlToArray($xmlStr)
    {
        if(!$xmlStr)
        {
            return [];
        }

        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $resArray = json_decode(json_encode(simplexml_load_string($xmlStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

        foreach ($resArray as $key => $value)
        {
            if (is_array($value))
            {
                if (empty($value))
                {
                    $resArray[$key] = '';
                }
            }
        }

        return $resArray;
    }

    protected static function callApiSource($apiUrl, array $get = [])
    {
        $data = self::getResponse($apiUrl, ['get' => $get, 'raw' => 1]);

        //如果存在，则说明返回错误
        if (FALSE !== strpos($data['res'], 'errcode'))
        {
            $res = json_decode($data['res'], true);

            if (isset($res['errcode']))
            {
                if ($res['errcode'] != 0)
                {
                    throw new \Exception("call weixin api error , code:{$res['errcode']}, msg:{$res['errmsg']}");
                }
            }
        }

        return $data;
    }
}