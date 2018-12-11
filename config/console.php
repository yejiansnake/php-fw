<?php

$dbConfig = require(__DIR__ . '/' . YII_ENV_CONFIG_DIR .'/db.php');
$params = require(__DIR__ . '/' . YII_ENV_CONFIG_DIR .'/params.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => $params['cache'],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => $params['mailer']['host'],
                'username' => $params['mailer']['username'],
                'password' => $params['mailer']['password'],
                'port' => $params['mailer']['port'],
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => $params['mailer']['from'],
            ],
        ],
    ],
    'params' => $params,
    'modules' => [
        'task' => [
            'class' => 'app\modules\task\BaseModule',
        ],
    ],
    'on beforeRequest' => function ($event)
    {
        \app\lib\bl\AppEvent::onBeforeRequest($event);
    },
    'on afterRequest' => function ($event)
    {
        \app\lib\bl\AppEvent::onAfterRequest($event);
    },
];

$config['components'] += $dbConfig;

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
