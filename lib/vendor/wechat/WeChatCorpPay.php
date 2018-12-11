<?php

namespace app\lib\vendor\wechat;

use yii\base\Exception;
use yii\helpers\VarDumper;

class WeChatCorpPay
{
    //---------------------------------------------------------------------------------------------------
    //接口

    const API_URL_PREFIX = 'https://api.mch.weixin.qq.com';     //API的前缀（域名和根路由）

    //企业付款给用户
    const API_CORP_PAY_TO_CLIENT = '/mmpaymkttransfers/promotion/transfers';    //企业付款
    const API_CORP_PAY_QUERY = '/mmpaymkttransfers/gettransferinfo';            //查询企业付款
    //---------------------------------------------------------------------------------------------------
    //参数名称

    const API_PARAM_NAME_APP_ID = 'appid';
    const API_PARAM_NAME_MCH_ID = 'mch_id';
    const API_PARAM_NAME_SIGN = 'sign';
    const API_PARAM_NAME_NONCE_STR = 'nonce_str';

    //---------------------------------------------------------------------------------------------------

    private $certPath = '';
    private $appID = '';
    private $mchID = '';
    private $apiKey = '';

    //---------------------------------------------------------------------------------------------------

    public function __construct(array $options = [])
    {
        $this->init($options);
    }

    protected function init(array $options)
    {
        if (empty($options['certPath'])
            || empty($options['appID'])
            || empty($options['mchID'])
            || empty($options['apiKey']))
        {
            throw new \InvalidArgumentException();
        }

        $this->certPath = $options['certPath'];
        $this->appID = $options['appID'];
        $this->mchID = $options['mchID'];
        $this->apiKey = $options['apiKey'];
    }

    //---------------------------------------------------------------------------------------------------
    //微信企业支付 API 方法

    public function corpPayToClient(array $params = [])
    {
        //商户订单号: partner_trade_no
        //用户openid: openid
        //校验用户姓名选项: check_name  NO_CHECK：不校验真实姓名   FORCE_CHECK：强校验真实姓名
        //收款用户姓名: re_user_name (可选) FORCE_CHECK 时必须
        //金额: amount 企业付款金额，单位为分
        //企业付款描述信息: desc
        //Ip地址: spbill_create_ip 	调用接口的机器Ip地址

        $params['mch_appid'] = $this->appID;
        $params['mchid'] = $this->mchID;
        return $this->callApi(self::API_CORP_PAY_TO_CLIENT, $params, true);
    }

    public function corpPayQuery(array $params = [])
    {
        //商户订单号: partner_trade_no

        $params[self::API_PARAM_NAME_APP_ID] = $this->appID;
        $params[self::API_PARAM_NAME_MCH_ID] = $this->mchID;
        return $this->callApi(self::API_CORP_PAY_QUERY, $params, true);
    }

    //---------------------------------------------------------------------------------------------------
    //内部方法

    protected function callApi($apiName, array $params, $useCert = false)
    {
        //检验并且组装参数
        if (!$this->wrapParams($params))
        {
            throw new \InvalidArgumentException();
        }

        //构造xml参数数据
        $xml = self::arrayToXml($params);
        $url = self::API_URL_PREFIX . $apiName;

        //TODO ...
        //var_dump($xml);
        //exit();

        //发送请求获取数据
        $data = $this->getResponse($url, $xml, $useCert);

        //转换xml为对象
        $resArray = self::xmlToArray($data);

        //检测返回的sign
        if ('SUCCESS' != $resArray['return_code'])
        {
            throw new \Exception("code:{$resArray['return_code']}, msg:{$resArray['return_msg']}");
        }

        return $resArray;
    }

    function checkResData($data)
    {
        $resArray = self::xmlToArray($data);

        //检测返回的sign
        if ('SUCCESS' != $resArray['return_code'])
        {
            throw new \Exception("code:{$resArray['return_code']}, msg:{$resArray['return_msg']}");
        }

        $sign = $this->createSign($resArray);

        if ($sign != $resArray[self::API_PARAM_NAME_SIGN])
        {
            throw new \Exception("response sign error, res:{$data}");
        }

        return true;
    }

    function getResponse($url, $xml, $useCert = false, $second = 30)
    {
        $ch = curl_init();

        curl_setopt($ch,CURLOPT_TIMEOUT,$second);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch,CURLOPT_URL,$url);

        if ($useCert)
        {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, 2);//严格校验

            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $this->certPath . '/apiclient_cert.pem');
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $this->certPath . '/apiclient_key.pem');
        }
        else
        {
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        curl_setopt($ch,CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $xml);

        $data = curl_exec($ch);

        if(empty($data))
        {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("get response error:{$error}");
        }

        curl_close($ch);

        return $data;
    }

    protected function createSign(array $params)
    {
        if (empty($params))
        {
            return '';
        }

        $res = '';

        ksort($params);

        foreach ($params as $key => $value)
        {
            if ($key == self::API_PARAM_NAME_SIGN
                || empty($key)
                || is_null($value)
                || $value === '')
            {
                continue;
            }

            if ($res == '')
            {
                $res .= "{$key}={$value}";
            }
            else
            {
                $res .= "&{$key}={$value}";
            }
        }

        $res .= "&key={$this->apiKey}";

        return strtoupper(md5($res));
    }

    protected function getSourceSignStr(array $params)
    {
        if (empty($params))
        {
            return '';
        }

        $res = '';

        ksort($params);

        foreach ($params as $key => $value)
        {
            if ($key == self::API_PARAM_NAME_SIGN
                || empty($key)
                || is_null($value)
                || $value === '')
            {
                continue;
            }

            if ($res == '')
            {
                $res .= "{$key}={$value}";
            }
            else
            {
                $res .= "&{$key}={$value}";
            }
        }

        $res .= "&key={$this->apiKey}";

        return $res;
    }

    protected static function createNonceStr()
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ($i = 0; $i < 32; $i++ )
        {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }

        return $str;
    }

    protected function wrapParams(array &$params)
    {
        if (empty($params))
        {
            return false;
        }

        $params[self::API_PARAM_NAME_NONCE_STR] = self::createNonceStr();

        $sign = $this->createSign($params);

        //TODO ...
        //var_dump($sign);

        $params[self::API_PARAM_NAME_SIGN] = $sign;

        return true;
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
            throw new \InvalidArgumentException();
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
}