<?php
/**
 * 公司信息
 */

namespace app\lib\bl\session;

use app\lib\common\session\BaseSession;

final class CompanySession extends BaseSession
{
    public static function getKey($keyParams = [])
    {
        return 'sys.sm.cp.base';
    }
}