<?php
/**
 * Created by PhpStorm.
 * User: snake
 * Date: 2018/11/4
 * Time: 16:42
 */

namespace app\lib\common\session;


abstract class BaseCurUserSession extends BaseSession
{
    public static function getKey($keyParams = [])
    {
        return 'sys.sm_user_info';
    }
}