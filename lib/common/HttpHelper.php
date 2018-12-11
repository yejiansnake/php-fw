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

    public static function outputFile($filePath, $outputFileName = null)
    {
        if (empty($filePath))
        {
            return;
        }

        $fileName = empty($outputFileName) ? basename($filePath) : $outputFileName;
        $encodedFilename = str_replace("+", "%20", urlencode($fileName));

        $userAgent = $_SERVER["HTTP_USER_AGENT"];

        header('Content-Length: ' . filesize($filePath));
        header('Content-type:' . mime_content_type($filePath));
        header("Content-Transfer-Encoding: binary");
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Pragma: public'); // HTTP/1.0
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");

        if (preg_match("/MSIE/", $userAgent)
            || preg_match("/Trident\/7.0/", $userAgent)
            || preg_match("/Edge/", $userAgent))
        {
            header('Content-Disposition: attachment; filename="' . $encodedFilename . '"');
        }
        else
        {
            if (preg_match("/Firefox/", $userAgent))
            {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '"');
            }
            else
            {
                header('Content-Disposition: attachment; filename="' . $fileName . '"');
            }
        }

        readfile($filePath);
    }

}

