<?php
/**
 * 权限信息
 * User: yejian
 * Date: 2016/2/25
 * Time: 17:49
 */

namespace app\lib\bl;

class Permission
{
    //----------------------------------------------------------------------------------------
    //权限

    const MGR_PMT_USER = 1;
    const MGR_PM_USER_VIEW = 101;

    //----------------------------------------------------------------------------------------
    //MGR 数据

    public static $MGR_DATA = [
        [
            'id' => self::MGR_PMT_USER,
            'name' => '用户管理',
            'items' => [
                [
                    'id' => self::MGR_PM_USER_VIEW,
                    'name' => '用户查看',
                ],
            ],
        ],
    ];
}