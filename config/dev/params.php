<?php

//--------------------------------------------------------------------------------

$appSysName = APP_SYS_NAME;
$sysDomain = APP_SYS_DOMAIN;

return [
    //-------------------------------------------------------------
    //系统进入维护状态为 true 候选人无法登录
    'suspend' => false,
    //-------------------------------------------------------------
    //基础配置
    'loadMgr' => true,                      //子系统初始化控制
    'pwdKey' => "!@bvfd%{$appSysName}f3$",    //创建密码的附加信息
    //-------------------------------------------------------------
    //Yii 默认邮件配置
    'mailer' => [
        'host' => 'smtp.exmail.qq.com',
        'username' => 'example',
        'password' => 'example',
        'port' => '25',
        'from' => ['example@qq.com' => 'example'],
    ],

    //-------------------------------------------------------------
    //session
    'session' => [
        'enable' => 0,
        'save_handler' => '',
        'save_path' => '',
//        'save_handler' => 'redis',
//        'save_path' => 'tcp://127.0.0.1:6379?auth=pwd',
    ],
    //-------------------------------------------------------------
    //缓存
    'cache' => [
        'keyPrefix' => APP_SYS_NAME,
        'class' => 'yii\caching\MemCache',
        'useMemcached' => true ,
        'servers' => [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 100,
            ],
        ],
    ],
    //-------------------------------------------------------------
    //文件存储管理
    'fileMgr' => [
        //类型: local 本地; db 数据库; cos 云存储;
        //local : 系统路径
        //db : 数据模型类名称（包含命名空间）
        //cos : 对象存储 bucket 的配置名称(系统的虚拟名称，非实际COS bucket 名称 )
        'image' => [
            'type' => 'local',
            'path' => "/data/resource/{$appSysName}/image", //路径
        ],
        //公开访问的图片存储配置
        'imagePub' => [
            'type' => 'local',
            'path' => APP_SYS_PATH . '/web/public', //路径
        ],
        'imageCorp' => [
            'type' => 'db',
            'path' => 'app\models\client\SysFileModel', //对应的存储类
        ],
        'audio' => [
            'type' => 'cos',
            'path' => 'audio',  //对应的 bucket
        ],
    ],

    //-------------------------------------------------------------
    //文件日志 配置(等级 4:调试; 3:信息; 2:警告; 1:错误; 0:不输出)
    'logMgr' => [
        //日志存储的绝对路径，如果没有配置则默认存储在当前系统的 @app/runtime/log 下
        'path' => "/resource/{$appSysName}/log",
        //日志类型
        'type' => [
            'sys' => YII_DEBUG ? 4 : 3,
        ],
    ],

    //-------------------------------------------------------------
    //微信
    'weChat' => [
        'id' => '',
        'appID' => '',
        'appSecret' => '',
        'token' => '',
        'encryptType' => 0,
        'aesKey' => '',
        'authHost' => "http://app.{$sysDomain}",
        //应用授权作用域
        'scope' => 1,
    ],
    //-------------------------------------------------------------
    //企业微信
    'corpWeChat' => [
        'corpID' => '',
        //应用Secret；自建应用时使用（每个应用一个，各不相同）
        'corpSecret' => '',
        //应用ID；自建应用时使用
        'agentID' => '',
        //事件相关：Token
        'token' => '',
        //事件相关：EncodingAESKey
        'aesKey' => '',
        //应用授权作用域
        'scope' => 1,
        'authHost' => "http://app.{$sysDomain}",
        'webAuthHost' => "http://admin.{$sysDomain}",
    ],

    //-------------------------------------------------------------
    //腾讯云相关
    'QCloud' => [
        'SMS' => [
            'appId' => '',
            'appKey' => '',
        ],
        //对象存储服务
        'COS' => [
            'region' => 'ap-shanghai',
            'appId' => '123456',
            'secretId' => '123456',
            'secretKey' => '123456',
            'expires' => '5',
            'bucket' => [
                'report' => 'dev-report',
                'image' => 'dev-image',
                'audio' => 'dev-audio',
                'video' => 'dev-video',
            ],
        ],
    ],
];