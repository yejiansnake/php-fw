<?php

namespace app\lib\common\file;

use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class DbFile extends BaseFile
{
    protected static $type = self::TYPE_DB;
    private $class = null;

    protected function init()
    {
        if (!class_exists($this->path)
        )
        {
            throw new ServerErrorHttpException('path invalid');
        }

        $this->class = $this->path;
    }

    public function get(array $params = [])
    {
        if (empty($params['key']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $model = $this->class::getOne(['key' => $params['key']]);

        if (empty($model))
        {
            return null;
        }

        return [
            'type' => $model->mine_type,
            'size' => $model->size,
            'data' => $model->data,
        ];
    }

    public function save(array $params = [])
    {
        if (empty($params['data']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $params['key'] = empty($params['key']) ? $this->createKey() : $params['key'];

        $model = new $this->class();

        $model->load([
            'type' => $this->name,
            'key' => $params['key'],
            'size' => strlen($params['data']),
            'mine_type' => empty($params['type']) ? 'application/octet-stream' : $params['type'],
            'data' => $params['data'],
        ]);

        if (!$model->save())
        {
            return null;
        }

        return [
            'key' => $params['key'],
            'path' => $params['key'],
        ];
    }

    public function exist(array $params = [])
    {
        $count = $this->class::getCount(['key' => $params['key']]);
        return empty($count) ? false : true;
    }

    public function remove(array $params = [])
    {
        $model = $this->class::getOne(['key' => $params['key']]);

        if (empty($model))
        {
            return false;
        }

        return $model->delete();
    }
}