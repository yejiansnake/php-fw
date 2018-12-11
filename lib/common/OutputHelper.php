<?php

namespace app\lib\common;

use yii\web\BadRequestHttpException;

class OutputHelper
{
    public static function download(array $params)
    {
        if (empty($params)
            || empty($params['path'])
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        if (!file_exists($params['path']))
        {
            throw new BadRequestHttpException('path invalid');
        }

        $path = $params['path'];
        $name = empty($params['name']) ? basename($params['path']) : $params['name'];

        $encodeName = str_replace("+", "%20", urlencode($name));
        header('Content-Length: ' . filesize($path));
        header('Content-type:' . mime_content_type($path));
        header("Content-Transfer-Encoding: binary");
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Pragma: public'); // HTTP/1.0
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");

        $ua = $_SERVER["HTTP_USER_AGENT"];
        if (preg_match("/MSIE/", $ua)
            || preg_match("/Trident\/7.0/", $ua)
            || preg_match("/Edge/", $ua)
        )
        {
            header('Content-Disposition: attachment; filename="' . $encodeName . '"');
        }
        else
        {
            if (preg_match("/Firefox/", $ua))
            {
                header('Content-Disposition: attachment; filename*="utf8\'\'' . $name . '"');
            }
            else
            {
                header('Content-Disposition: attachment; filename="' . $name . '"');
            }
        }

        readfile($path);
    }
}