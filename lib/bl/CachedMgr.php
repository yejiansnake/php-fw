<?php

namespace app\lib\bl;

use app\lib\common\CachedHelper;

class CachedMgr extends CachedHelper
{
    public static function getCompanyMgrKey($token)
    {
        return "CP_MGR_{$token}";
    }
}