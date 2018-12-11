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
		
        $info = 'class_app\\lib\\common';
        $uid = uniqid(time() . '' . rand(), true);
        $res = strtoupper(md5($uid . $info . $extra));
        return $res;
    }
	
	public static function ToStringWeb($add = '')
	{
        if (empty($add))
        {
            $add = '';
        }

        $extra = 'getStringWeb' . $add;
        $extra .= empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'];
        $extra .= empty($_SERVER['REQUEST_TIME']) ? '' : $_SERVER['REQUEST_TIME'];
        $extra .= empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
        $extra .= empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'];
        $extra .= empty($_SERVER['SERVER_PORT']) ? '' : $_SERVER['SERVER_PORT'];
        $extra .= empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
        $extra .= empty($_SERVER['REMOTE_PORT']) ? '' : $_SERVER['REMOTE_PORT'];
		return self::toString($extra);
	}
}