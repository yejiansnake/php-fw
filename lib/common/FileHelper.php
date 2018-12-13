<?php

namespace app\lib\common;

use Yii;
use yii\web\ServerErrorHttpException;
use yii\web\BadRequestHttpException;
use app\lib\common\file\BaseFile;

class FileHelper
{
    private $obj = null;

    private static $map = [
        BaseFile::TYPE_LOCAL => '\app\lib\common\file\LocalFile',
        BaseFile::TYPE_DB => '\app\lib\common\file\DbFile',
        BaseFile::TYPE_COS => '\app\lib\common\file\CosFile',
    ];

    public function __construct($name)
    {
         $config = BaseFile::config($name);

         if (array_key_exists($config['type'], self::$map))
         {
             $class = self::$map[$config['type']];
             $this->obj = new $class();
         }

         throw new ServerErrorHttpException('config invalid');
    }

    public function get(array $params = [])
    {
        if (empty($params['cache']))
        {
            if (empty($params['key']))
            {
                throw new BadRequestHttpException('params invalid');
            }

            $thisObj = $this->obj;
            return CachedHelper::getOrSet($this->cacheKey($params), function () use ($thisObj, $params)  {
                return $thisObj->get($params);
            });
        }

        return $this->obj->get($params);
    }

    public function save(array $params = [])
    {
        $res = $this->obj->save($params);

        if (!empty($res) && isset($params['cache']))
        {
            CachedHelper::delete($this->cacheKey($params));
        }

        return $res;
    }

    public function exist(array $params = [])
    {
        return $this->obj->exist($params);
    }

    public function remove(array $params = [])
    {
        if (isset($params['cache']))
        {
            if (empty($params['key']))
            {
                throw new BadRequestHttpException('params invalid');
            }

            CachedHelper::delete($this->cacheKey($params));
        }

        return $this->obj->remove($params);
    }

    private function cacheKey(array $params)
    {
        $name = $this->obj->name();
        $appSysName = APP_SYS_NAME;
        return "{$appSysName}-FileMgr-{$name}-{$params['key']}";
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