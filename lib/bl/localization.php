<?php
/**
 * API 接口抛出异常文字本地化语言转义
 */

namespace app\lib\bl;

class localization
{
    private static $map = [
        //通用
        'not login' => '未登录',
        'params invalid' => '参数错误',
        'entity not found' => '数据没有找到',
        'pic save failed' => '图片保存失败',
        'entity save failed' => '保存失败',
        'entity delete failed' => '删除失败',
        'user name or password error' => '用户名或密码错误',
        'two input is inconsistent' => '两次输入不一致',
        'password error' => '密码错误',
        'pwd update failed' => '更新密码失败',

        'user invalid' => '无效用户',

        'user not resource' => '没有访问该资源的权限',
        'resource out of limit' => '资源权限超出范围',
        'role out of limit' => '角色权限超出范围',
        'company not exist' => '客户信息不存在',

        //业务
        'wx auth invalid' => '微信服务号授权无效',
        'wx auth not exist' => '微信服务号授权不存在',
    ];

    public static function getValue($key)
    {
        return array_key_exists($key, self::$map) ? self::$map[$key] : $key;
    }
}