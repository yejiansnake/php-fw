<?php
/**
 * web请求（http/https）
 * User: yejian
 * Date: 2016/3/21
 * Time: 14:17
 */

namespace app\lib\common;

class WebRequest
{
    public static function getResponseByGet($url, array $getParams = null, array $options = [], $ssl = null)
    {
        $params = [];

        if (!empty($getParams))
        {
            $params['get'] = $getParams;
        }

        $options[CURLOPT_HTTPGET] = 1;
        $params['options'] = $options;
        $params['ssl'] = $ssl;

        return self::getResponse($url, $params);
    }

    public static function getResponseByJson($url, $postJson, array $queryParams = null, array $options = [], $ssl = null)
    {
        $options[CURLOPT_HTTPHEADER] = ['Content-Type: application/json'];
        return self::getResponseByPost($url, json_encode($postJson, JSON_UNESCAPED_UNICODE), $queryParams, $options, $ssl);
    }

    public static function getResponseByPostJson($url, $postParams, array $getParams = null, array $options = [], $ssl = null)
    {
        return self::getResponseByPost($url, json_encode($postParams, JSON_UNESCAPED_UNICODE), $getParams, $options, $ssl);
    }

    public static function getResponseByPost($url, $postParams, array $getParams = null, array $options = [], $ssl = null)
    {
        $params = [];

        if (!empty($getParams))
        {
            $params['get'] = $getParams;
        }

        if (!empty($postParams))
        {
            $params['post'] = $postParams;
        }

        $params['options'] = $options;
        $params['ssl'] = $ssl;

        return self::getResponse($url, $params);
    }

    public static function getResponse($url, array $params = [])
    {
        $ssl = isset($params['ssl']) ? $params['ssl'] : null;
        $options = isset($params['options']) ? $params['options'] : [];
        $options = is_array($options) ? $options : [];

        if (!empty($params['get']))
        {
            $queryStr = http_build_query($params['get']);
            $url .= strrchr($url, '?') ? $queryStr : ('?' . $queryStr);
        }

        $defaults = [
            CURLOPT_HEADER => false,
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
        ];

        if (!empty($params['post'])) {
            $defaults[CURLOPT_POST] = TRUE;
            $defaults[CURLOPT_POSTFIELDS] = $params['post'];
        }

        if (isset($ssl))
        {
            if (empty($ssl) || !isset($ssl['cert_path']) || !isset($ssl['key_path']))
            {
                $options[CURLOPT_SSL_VERIFYPEER] = false;
                $options[CURLOPT_SSL_VERIFYHOST] = false;
            }
            else
            {
                $options[CURLOPT_SSL_VERIFYPEER] = TRUE;
                $options[CURLOPT_SSL_VERIFYHOST] = 2;   //严格校验

                $options[CURLOPT_SSLCERTTYPE] = 'PEM';
                $options[CURLOPT_SSLCERT] = $ssl['cert_path'];
                $options[CURLOPT_SSLKEYTYPE] = 'PEM';
                $options[CURLOPT_SSLKEY] = $ssl['key_path'];;
            }
        }

//        print_r($defaults);
//        echo "\n\n";
//        print_r($defaults + $options);
//        print_r($options);
//        exit;

        $optArray = $defaults + $options;

        $connObj = \curl_init();
        \curl_setopt_array($connObj, $optArray);
        $result = \curl_exec($connObj);

        $res = [];

        if ($result === false)
        {
            $errno = curl_errno($connObj);
            $error = curl_error($connObj);
            $res = ['success' => false, 'errno' => $errno, 'error' => $error];
        }
        else
        {
            $httpInfo = curl_getinfo($connObj);
            $res = ['success' => true, 'data' => $result, 'info' => $httpInfo];
        }

        \curl_close($connObj);

        return $res;
    }
}