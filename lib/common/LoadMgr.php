<?php
/**
 * 加载管理器.
 */

namespace app\lib\common;

class LoadMgr
{
    const FILTER_TYPE_NAME = 0;
    const FILTER_TYPE_APP_ID = 1;

    public static $config = [];
    public static $sysModule = '';
    public static $type = 0;
    public static $subSysName = '';
    public static $curRoute = '';

    public static function isEnable()
    {
        if (empty(\Yii::$app->params['loadMgr']))
        {
            return false;
        }

        return true;
    }

    private static function isLocalHost()
    {
        $sourceDomain = explode(':', $_SERVER['HTTP_HOST'])[0];   //过滤掉端口号

        if (empty($sourceDomain))
        {
            return false;
        }

        //如果为localhost也不处理
        if ($sourceDomain == 'localhost'
            && $_SERVER['REMOTE_ADDR'] == '127.0.0.1')
        {
            return true;
        }

        return false;
    }

    public static function init()
    {
        if (self::isLocalHost())
        {
            return true;
        }

        self::$config = require(APP_SYS_PATH . '/config/load.php');
        $domain = $_SERVER['HTTP_HOST'];

        //m.test.bbb.zzzzz.com:60080
        //也可用正则表达式直接取
        $domain = explode(':', $domain)[0];   //过滤掉端口号
        if (empty($domain))
        {
            return false;
        }

        $domainParser = new DomainParser();

        if (!$domainParser->load($domain))
        {
            if (YII_DEBUG)
            {
                exit("{$domain} not support parser");
            }
            return false;
        }

        //路由起始域名等级
        if (!empty(self::$config['min_level']))
        {
            $min_level = self::$config['min_level'];
            if ($domainParser->getValidCount() < ($min_level + 1))
            {
                return true;
            }
        }

        $sysDispatcher = new SysDispatcher();
        if (!$sysDispatcher->setRoutes(self::$config['routes']))
        {
            if (YII_DEBUG) exit('sysDispatcher->setRoutes');
            return false;
        }

        self::$curRoute = $sysDispatcher->getRoute($domain);

        if (empty(self::$curRoute)
            || empty(self::$curRoute['module']))
        {
            if (YII_DEBUG) exit("无效模块：{$domain}");
            return false;
        }

        self::$sysModule = self::$curRoute['module'];
        self::$type = empty(self::$curRoute['type']) ? null : self::$curRoute['type'];

        if (isset(self::$curRoute['sub_sys']))
        {
            if (self::$curRoute['sub_sys'] > 1)
            {
                $subSysIndex = self::$curRoute['sub_sys'];
                self::$subSysName = $domainParser->getName($subSysIndex);
            }
            else if (self::$curRoute['sub_sys'] == -1)
            {
                if (!empty($_SERVER['HTTP_KEY_NAME']))
                {
                    self::$subSysName = $_SERVER['HTTP_KEY_NAME'];
                }
            }
        }

        return true;
    }
}