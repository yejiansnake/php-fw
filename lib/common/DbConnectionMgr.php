<?php

namespace app\lib\common;

use yii\db\Connection;

class DbConnectionMgr
{
    private static $dbConnMap = [];

    public static function getDbConn(array $config = [])
    {
        if (empty($config))
        {
            return null;
        }

        if (!isset($config['ip'])
            || !isset($config['port'])
            || !isset($config['name'])
            || !isset($config['user'])
            || !isset($config['pwd']))
        {
            return null;
        }

        $dbKey = "{$config['ip']}_{$config['port']}_{$config['name']}_{$config['user']}_{$config['pwd']}";
        $dbConn = null;

        if (array_key_exists($dbKey, self::$dbConnMap))
        {
            $dbConn = self::$dbConnMap[$dbKey];
        }
        else
        {
            $connConfig = [
                'dsn' => "mysql:host={$config['ip']};port={$config['port']};dbname={$config['name']}",
                'username' => $config['user'],
                'password' => $config['pwd'],
                'charset' => 'utf8mb4',
            ];

            $dbConn = new Connection($connConfig);
            self::$dbConnMap[$dbKey] = $dbConn;
        }

        return $dbConn;
    }
}