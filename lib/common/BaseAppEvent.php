<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2018/11/4
 * Time: 16:11
 */

namespace app\lib\common;


abstract class BaseAppEvent
{
    public static function onBeforeRequest($event)
    {
        if (defined('YII_CONSOLE'))
        {
            return;
        }

        self::initLoadMgr();

        static::initCompany();
    }

    public static function onAfterRequest($event)
    {

    }

    private static function initLoadMgr()
    {
        if (LoadMgr::isEnable())
        {
            if (!LoadMgr::init())
            {
                if (YII_DEBUG) echo 'LoadMgr::init failed';

                //这里返回403、404 或者直接跳转到 404 页面
                header('HTTP/1.1 404 Not Found');
                header("status: 404 Not Found");
                //http_redirect();
                exit;
            }
        }
    }

    protected abstract static function initCompany();
}