<?php

namespace app\lib\bl;

class Permission
{
    //----------------------------------------------------------------------------------------
    //admin

    const ADMIN_PM_USER_VIEW = 1;
    const ADMIN_PM_USER = 2;
    const ADMIN_PM_ROLE = 3;

    public static $ADMIN_DATA = [
        [
            'id' => self::ADMIN_PM_USER_VIEW,
            'name' => '用户查看',
        ],
        [
            'id' => self::ADMIN_PM_USER,
            'name' => '用户管理',
        ],
        [
            'id' => self::ADMIN_PM_ROLE,
            'name' => '角色管理',
        ],
    ];

    //----------------------------------------------------------------------------------------
    //mgr

    const MGR_PM_USER = 1;

    public static $MGR_DATA = [
        [
            'id' => self::MGR_PM_USER,
            'name' => '用户管理',
        ],
    ];


}