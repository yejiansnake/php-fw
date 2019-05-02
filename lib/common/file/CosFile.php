<?php

namespace app\lib\common\file;

use yii\web\BadRequestHttpException;
use app\lib\vendor\QCloud\CosClient;

class CosFile extends BaseFile
{
    protected static $type = self::TYPE_COS;

    protected function init()
    {

    }

    public function get(array $params = [])
    {
        if (empty($params['key']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $res = CosClient::headObject($params);

        if (empty($res))
        {
            return null;
        }

        return [
            'type' => $res['ContentType'],
            'size' => $res['ContentLength'],
            'data' => isset($params['simple']) ? null : file_get_contents(CosClient::getSignatureObjectUrl($params)),
        ];
    }

    public function save(array $params = [])
    {
        if (empty($params['data']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $params['key'] = empty($params['key']) ? $this->createKey() : $params['key'];

        $res = CosClient::uploadObject([
            'bucket' => $this->path,
            'key' => $params['key'],
            'data' => $params['data'],
        ]);

        if (empty($res) || empty($res['ObjectURL']))
        {
            return null;
        }

        return [
            'key' => $params['key'],
            'path' => str_replace('http://', 'https://', $res['ObjectURL']),
        ];
    }

    public function exist(array $params = [])
    {
        $res = CosClient::headObject($params);

        return empty($res) ? false : true;
    }

    public function remove(array $params = [])
    {
        $res = CosClient::deleteObject([
            'bucket' => $this->path,
            'key' => $params['key'],
        ]);

        return empty($res['RequestId']) ? false : true;
    }
}



