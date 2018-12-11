<?php
namespace app\lib\vendor\QCloud;

use Yii;
use yii\web\BadRequestHttpException;
use Qcloud\Cos\Client;

abstract class CosClient
{
    CONST BUCKET_KEY_REPORT = 'report';
    CONST BUCKET_KEY_IMAGE = 'image';
    CONST BUCKET_KEY_AUDIO = 'audio';
    CONST BUCKET_KEY_VIDEO = 'video';

    public static $buckets = [
        self::BUCKET_KEY_REPORT,
        self::BUCKET_KEY_IMAGE,
        self::BUCKET_KEY_AUDIO,
        self::BUCKET_KEY_VIDEO
    ];

    private static $client = null;
    private static $config = null;

    private static function config()
    {
        if (empty(self::$config))
        {
            if (empty(Yii::$app->params['QCloud'])
                || empty(Yii::$app->params['QCloud']['COS'])
            )
            {
                throw new BadRequestHttpException('object cloud config invalid');
            }

            $config = Yii::$app->params['QCloud']['COS'];

            if (empty($config['region'])
                || empty($config['appId'])
                || empty($config['secretId'])
                || empty($config['secretKey']))
            {
                throw new BadRequestHttpException('not found configuration file');
            }

            self::$config = $config;
        }

        return self::$config;
    }

    private static function client()
    {
        if (empty(self::$client))
        {
            $config = self::config();

            self::$client = new Client([
                    'region' => $config['region'],
                    'credentials' => [
                        'appId' => $config['appId'],
                        'secretId' => $config['secretId'],
                        'secretKey' => $config['secretKey']
                    ]
                ]
            );
        }

        return self::$client;
    }

    private static function getBucketName($key)
    {
        $config = self::config();

        if (empty($config['bucket']))
        {
            throw new BadRequestHttpException('object cloud bucket config invalid');
        }

        $map = $config['bucket'];

        if (!array_key_exists($key, $map))
        {
            throw new BadRequestHttpException("object cloud bucket key:{$key} invalid");
        }

        return $map[$key];
    }

    public static function uploadObject(array $params)
    {
        if (empty($params)
            || empty($params['bucket'])
            || empty($params['key'])
            || empty($params['data']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        try
        {
            return self::client()->upload(
                $bucket = self::getBucketName($params['bucket']),
                $key = $params['key'],
                $body = $params['data']
            );
        }
        catch (\Exception $ex)
        {
            return null;
        }
    }

    public static function headObject(array $params)
    {
        if (empty($params)
            || empty($params['bucket'])
            || empty($params['key'])
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        try
        {
            return self::client()->headObject([
                'Bucket' => self::getBucketName($params['bucket']),
                'Key' => $params['key'],
            ]);
        }
        catch (\Exception $ex)
        {
            return null;
        }
    }

    public static function getObjectUrl(array $params)
    {
        if (empty($params)
            || empty($params['bucket'])
            || empty($params['key']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        return self::client()->getObjectUrl(
            self::getBucketName($params['bucket']),
            $params['key']
        );
    }

    public static function getSignatureObjectUrl(array $params)
    {
        if (empty($params)
            || empty($params['bucket'])
            || empty($params['key']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        return self::client()->getSignatureObjectUrl(
            self::getBucketName($params['bucket']),
            $params['key'],
            self::$config['expires']
        );
    }

    public static function getObjectList(array $params = [])
    {
        if (empty($params)
            || empty($params['bucket'])
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        try
        {
            return self::client()->listObjects(['Bucket' => self::getBucketName($params['bucket'])]);
        }
        catch (\Exception $ex)
        {
            return null;
        }
    }

    public static function deleteObject(array $params = [])
    {
        if (empty($params)
            || empty($params['bucket'])
            || empty($params['key'])
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        try
        {
            return self::client()->deleteObject([
                'Bucket' => self::getBucketName($params['bucket']),
                'Key' => $params['key']
            ]);
        }
        catch (\Exception $ex)
        {
            return null;
        }
    }
}