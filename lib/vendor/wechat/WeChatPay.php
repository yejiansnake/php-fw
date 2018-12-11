<?php

namespace app\lib\vendor\wechat;

use yii\base\Exception;
use yii\helpers\VarDumper;

class WeChatPay
{
    //---------------------------------------------------------------------------------------------------
    //接口
    const API_URL_PREFIX = 'https://api.mch.weixin.qq.com';     //API的前缀（域名和根路由）

    //支付
    const API_NAME_UNIFIED_ORDER = '/pay/unifiedorder';         //统一下单
    const API_NAME_ORDER_QUERY = '/pay/orderquery';             //查询订单
    const API_NAME_CLOSE_ORDER = '/pay/closeorder';             //关闭订单
    const API_NAME_REFUND = '/secapi/pay/refund';               //申请退款
    const API_NAME_REFUND_QUERY = '/pay/refundquery';           //查询退款
    const API_NAME_DOWNLOAD_BILL = '/pay/downloadbill';         //下载对账单
    const API_NAME_REPORT = '/payitil/report';                  //测速上报

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
    private $notifyUrl = '';

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
            || empty($options['apiKey'])
            || empty($options['notifyUrl']))
        {
            throw new \InvalidArgumentException();
        }

        $this->certPath = $options['certPath'];
        $this->appID = $options['appID'];
        $this->mchID = $options['mchID'];
        $this->apiKey = $options['apiKey'];
        $this->notifyUrl = $options['notifyUrl'];
    }

    //---------------------------------------------------------------------------------------------------
    //微信支付 API 方法

    public function unifiedOrder(array $params)
    {
        $params['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
        $params['notify_url'] = $this->notifyUrl;
        $params['device_info'] = 'WEB';
        $params['trade_type'] = 'JSAPI';
        return $this->callApi(self::API_NAME_UNIFIED_ORDER, $params, false);
    }

    public function orderQuery(array $params)
    {
        return $this->callApi(self::API_NAME_ORDER_QUERY, $params, false);
    }

    public function closeOrder(array $params)
    {
        return $this->callApi(self::API_NAME_CLOSE_ORDER, $params, false);
    }

    public function refund(array $params)
    {
        $params['op_user_id'] = $this->mchID;
        return $this->callApi(self::API_NAME_REFUND, $params, true);
    }

    public function refundQuery(array $params)
    {
        return $this->callApi(self::API_NAME_REFUND_QUERY, $params, false);
    }

    public function downloadBill(array $params)
    {
        return $this->callApi(self::API_NAME_DOWNLOAD_BILL, $params, false);
    }

    public function report(array $params = [])
    {
        return $this->callApi(self::API_NAME_REPORT, $params, false);
    }

    public function getJsPayParams($prepay_id)
    {
        $params = [
            'appId' => $this->appID,
            'timeStamp' => '' . time(),
            'nonceStr' => self::createNonceStr(),
            'package' => 'prepay_id=' . $prepay_id,
            'signType' => 'MD5',
        ];

        $params['paySign'] = $this->createSign($params);

        return $params;
    }

    //---------------------------------------------------------------------------------------------------

    //返回 notify 的对象
    //如果通知失败，或者校验签名失败时，则 throw 异常
    public function getNotify($data)
    {
        //转换xml为对象
        $resArray = self::xmlToArray($data);

        //检测返回的sign
        if ('SUCCESS' != $resArray['return_code'])
        {
            throw new \Exception("code:{$resArray['return_code']}, msg:{$resArray['return_msg']}");
        }

        if (!$this->checkSign($resArray))
        {
            throw new \Exception('response sign error');
        }

        return $resArray;
    }

    //返回确认信息给微信服务器
    public static function responseNotify($isSuccess = true)
    {
        if ($isSuccess)
        {
            die('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
        }
        else
        {
            die('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>');
        }
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

        if (!$this->checkSign($resArray))
        {
            throw new \Exception("response sign error, res:{$data}");
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

    protected function checkSign($resArray)
    {
        if ($this->createSign($resArray) != $resArray[self::API_PARAM_NAME_SIGN])
        {
            return false;
        }

        return true;
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

        $params[self::API_PARAM_NAME_APP_ID] = $this->appID;
        $params[self::API_PARAM_NAME_MCH_ID] = $this->mchID;
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