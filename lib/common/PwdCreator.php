<?php
/**
 * 密码创建者
 */

namespace app\lib\common;

abstract class PwdCreator
{
    const TYPE_MD5 = 0;     //默认MD5
    const TYPE_SHA1 = 1;
    const TYPE_SHA256 = 2;
    const TYPE_SHA512 = 3;

    private static $algoMap = [
        self::TYPE_MD5 => 'md5',
        self::TYPE_SHA1 => 'sha1',
        self::TYPE_SHA256 => 'sha256',
        self::TYPE_SHA512 => 'sha512',
    ];

    public static function getTypes()
    {
        return [
            'MD5' => self::TYPE_MD5,
            'SHA1' => self::TYPE_SHA1,
            'SHA256' => self::TYPE_SHA256,
            'SHA512' => self::TYPE_SHA512,
        ];
    }

    public static function get($data)
    {
        $key = null;
        if (!empty(\Yii::$app->params['pwdKey']))
        {
            $key = \Yii::$app->params['pwdKey'];
        }

        return self::create(self::TYPE_SHA256, $data, $key);
    }

    public static function create($type, $data, $key = null)
    {
        if (empty($type) || empty($data))
        {
            throw new \Exception('params invalid');
        }

        if (!array_key_exists($type, self::$algoMap))
        {
            throw new \Exception('crypt type invalid');
        }

        $algo = self::$algoMap[$type];

        $temp = $data;
        if (!empty($key))
        {
            $temp .= $key;
        }

        $pwd = hash($algo, $temp);

        return (sprintf("%02d", $type) . $pwd);
    }
}