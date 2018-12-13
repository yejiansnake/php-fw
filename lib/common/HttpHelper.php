<?php
/**
 * HTTP 辅助
 */
namespace app\lib\common;

class HttpHelper
{
    public static function wrapUrl($url, array $getParams = null)
    {
        if (empty($url))
        {
            $url = '';
        }

        if (!empty($getParams)) {
            $queryStr = http_build_query($getParams);
            if (strrchr($url, '?')) {
                $url .= $queryStr;
            } else {
                $url .= '?' . $queryStr;
            }
        }

        return $url;
    }

    public static function getProtocol()
    {
        return ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
                && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'))
            ? 'https:' : 'http:';
    }
}

