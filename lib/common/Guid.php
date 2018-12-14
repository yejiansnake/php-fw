<?php
/**
 * 唯一字符串生成器
 * User: yejian
 * Date: 2016/2/29
 * Time: 11:09
 */

namespace app\lib\common;

class Guid
{
    public static function toString($extra = '')
    {
		if (is_null($extra))
		{
			$extra = '';
		}

        $uid = uniqid('', true);
        $str = __FILE__ . microtime() . mt_rand() . $uid . $extra;
        $sha1 = sha1($str);
        $res = strtoupper($sha1);
        return $res;
    }
	
	public static function ToStringWeb($add = '')
	{
        if (empty($add))
        {
            $add = '';
        }

        $extra = session_id() . $add;
        $extra .= empty($_SERVER['HTTP_HOST']) ? '' . mt_rand() : $_SERVER['HTTP_HOST'];
        $extra .= empty($_SERVER['REQUEST_TIME']) ? '' . mt_rand() : $_SERVER['REQUEST_TIME'];
        $extra .= empty($_SERVER['HTTP_USER_AGENT']) ? '' . mt_rand() : $_SERVER['HTTP_USER_AGENT'];
        $extra .= empty($_SERVER['SERVER_ADDR']) ? '' . mt_rand() : $_SERVER['SERVER_ADDR'];
        $extra .= empty($_SERVER['SERVER_PORT']) ? '' . mt_rand() : $_SERVER['SERVER_PORT'];
        $extra .= empty($_SERVER['REMOTE_ADDR']) ? '' . mt_rand() : $_SERVER['REMOTE_ADDR'];
        $extra .= empty($_SERVER['REMOTE_PORT']) ? '' . mt_rand() : $_SERVER['REMOTE_PORT'];
        return self::toString($extra);
	}
}