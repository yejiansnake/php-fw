<?php


if (file_exists(APP_SYS_PATH . '/my.prod')) {
    defined('YII_ENV') or define('YII_ENV', 'prod');
}
else {
    defined('YII_ENV') or define('YII_ENV', 'dev');
}

$envConfigDirName = '';

if (!empty($envConfigDirName))
{
    define('YII_ENV_CONFIG_DIR', $envConfigDirName);
}
else
{
    define('YII_ENV_CONFIG_DIR', YII_ENV);
}

require(__DIR__ . '/' . YII_ENV_CONFIG_DIR .'/env.php');