<?php

namespace app\lib\common\file;

use Yii;
use \yii\web\BadRequestHttpException;
use app\lib\common\Guid;

abstract class BaseFile
{
    const TYPE_LOCAL = 'local';
    const TYPE_DB = 'db';
    const TYPE_COS = 'cos';

    protected static $type = null;
    protected $config = null;
    protected $name = null;
    protected $path = null;

    public function __construct($name)
    {
        $this->config = self::config($name);

        if (self::$type != $this->config['type'])
        {
            throw new BadRequestHttpException('config invalid');
        }

        $this->name = $name;
        $this->path = $this->config['path'];

        $this->init();
    }

    public static function config($name)
    {
        if (empty($name)
            || empty(Yii::$app->params['fileMgr'])
            || empty(Yii::$app->params['fileMgr'][$name])
        )
        {
            throw new BadRequestHttpException('params or config invalid');
        }

        $config = Yii::$app->params['fileMgr'][$name];

        if (is_array($config)
            && !isset($config['type'])
            && !isset($config['path'])
        )
        {
            return $config;
        }

        throw new BadRequestHttpException('config invalid');
    }

    public static function decodeBase64Image($content)
    {
        //上传的内容：data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADYAAAAwCAYAAABaHInAAAAAGXRFWHRTb2Z0d2

        $strArray = explode(',', $content);
        $data = base64_decode($strArray[1]);

        $descArray = explode(';', $strArray[0]);
        $miniType = explode(':', $descArray[1]);

        return [
            'data' => $data,
            'size' => strlen($data),
            'type' => $miniType,
        ];
    }

    public static function encodeBase64Image($content, $miniType)
    {
        $base64 = base64_encode($content);
        return "data:{$miniType};base64,{$base64}";
    }

    protected function createKey()
    {
        $key = Guid::ToStringWeb($this->name . $this->path);
        return "{$this->name}-{$key}";
    }

    public function name()
    {
        return $this->name;
    }

    abstract protected function init();

    abstract public function exist(array $params = []);

    abstract public function get(array $params = []);

    abstract public function save(array $params = []);

    abstract public function remove(array $params = []);
}