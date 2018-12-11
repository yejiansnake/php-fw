<?php
/**
 * LoadMgr 预加载配置文件
 */

$domain = str_replace('.', '\.', APP_SYS_DOMAIN);

$routes = [
    //管理端
    "^admin\.{$domain}$" => [
        'module' => 'admin',
        'default' => '/admin/',
        'login' => '/admin/',
    ],
    //企业微信
    "^app\.{$domain}$" => [
        'module' => 'app',
        'default' => '/app/',
        'login' => '/app/',
    ],
];

return [
    /*
     * 路由信息结构：
     * key: 域名的路由正则表达式
     * value:
     *      module : 对应的模块
     * */
    'routes' => $routes,

    //路由起始数
    'min_level' => 3,

    //子系统对应的域名层级(0 开始)
//    'sub_sys' => [
//        'enable' => 1,
//        'level' => 4,
//    ],
];