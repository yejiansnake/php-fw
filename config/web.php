<?php

$dbConfig = require(__DIR__ . '/' . YII_ENV_CONFIG_DIR .'/db.php');
$params = require(__DIR__ . '/' . YII_ENV_CONFIG_DIR .'/params.php');

if (!empty($params['session']) && !empty($params['session']['enable']))
{
    ini_set("session.save_handler", $params['session']['save_handler']);
    ini_set("session.save_path", $params['session']['save_path']);
}

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'language' => 'zh-CN',
    'bootstrap' => [],
    'components' => [
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => false,
        ],
        'request' => [
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ]
        ],
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
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
                //Restful rules
                'GET api/<module:[\w,-]+>/<controller:[\w,-]+>' => 'api/<module>/<controller>/index',
                'GET,HEAD api/<module:[\w,-]+>/<controller:[\w,-]+>/<id:\d+>' => 'api/<module>/<controller>/view',
                'POST api/<module:[\w,-]+>/<controller:[\w,-]+>' => 'api/<module>/<controller>/create',
                'PATCH,PUT api/<module:[\w,-]+>/<controller:[\w,-]+>/<id:\d+>' => 'api/<module>/<controller>/update',
                'DELETE api/<module:[\w,-]+>/<controller:[\w,-]+>/<id:\d+>' => 'api/<module>/<controller>/delete',
                'OPTIONS api/<module:[\w,-]+>/<controller:[\w,-]+>/<id:\d+>' => 'api/<module>/<controller>/options',

                //微信第三方服务商 客户公众号时间接收处理
                'event/wechat-comp-app/receive/<appID:[\w,-]+>' => 'event/wechat-comp-app/receive',

                //rules
                'api/<module:[\w,-]+>/<controller:[\w,-]+>/<action:[\w,-]+>/<id:\d+>' => 'api/<module>/<controller>/<action>',
                '<module:[\w,-]+>/<controller:[\w,-]+>/<action:[\w,-]+>/<id:\d+>' => '<module>/<controller>/<action>',
                '<controller:[\w,-]+>/<action:[\w,-]+>/<id:\d+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\BaseModule',
        ],
        'event' => [
            'class' => 'app\modules\event\BaseModule',
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
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
