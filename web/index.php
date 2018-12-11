<?php

//---------------------------------------------------------------------

function is_session_started()
{
    if ( php_sapi_name() !== 'cli' )
    {
        if ( version_compare(phpversion(), '5.4.0', '>='))
        {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        }
        else
        {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}

//---------------------------------------------------------------------

//获取当前的系统目录（默认index.php 放在最后的 /web 中）]
define('APP_SYS_PATH', substr(str_replace('\\', '/', __DIR__), 0, -4));

require(__DIR__ . '/../config/env.php');

require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
$config = require(__DIR__ . '/../config/web.php');

//Yii 启动主体---------------------------------------------------------

if (is_session_started() === FALSE) session_start();

(new yii\web\Application($config))->run();