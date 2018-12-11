<?php

namespace app\lib\common\file;

use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

class LocalFile extends BaseFile
{
    protected static $type = self::TYPE_LOCAL;

    protected function init()
    {
        if (file_exists($this->path))
        {
            if (is_file($this->path))
            {
                unlink($this->path);
            }
            else
            {
                return;
            }
        }

        if (!mkdir($this->path, 0777, true))
        {
            throw new ServerErrorHttpException('path invalid');
        }
    }

    private function fullPath(array $params = [])
    {
        if (empty($params['key']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        return "{$this->path}/{$params['key']}";
    }

    public function get(array $params = [])
    {
        if (empty($params['key']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $filePath = $this->fullPath($params);

        if (!file_exists($filePath))
        {
            return null;
        }

        return [
            'type' => filesize($filePath),
            'size' => mime_content_type($filePath),
            'data' => isset($params['simple']) ? null : file_get_contents($filePath),
        ];
    }

    public function save(array $params = [])
    {
        if (empty($params['data']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $params['key'] = empty($params['key']) ? $this->createKey() : $params['key'];

        $fullPath = $this->fullPath($params);
        if (false === file_put_contents($fullPath, $params['data']))
        {
            return null;
        }

        return [
            'key' => $params['key'],
            'path' => $fullPath,
        ];
    }

    public function exist(array $params = [])
    {
        return file_exists($this->fullPath($params));
    }

    public function remove(array $params = [])
    {
        return unlink($this->fullPath($params));
    }
}