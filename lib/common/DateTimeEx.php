<?php
/**
 * 扩展版本的DateTime
 * User: Administrator
 * Date: 2016/3/24
 * Time: 19:59
 */

namespace app\lib\common;

class DateTimeEx extends \DateTime
{
    const DATE_FORMAT = 'Y-m-d';
    const DATE_SHORT_FORMAT = 'Ymd';
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';
    const DATE_TIME_SP_FORMAT = 'YmdHis';

    //change: array, change: http://php.net/manual/en/function.strtotime.php
    public static function getString(array $params = [])
    {
        $time = self::getUnix($params);
        $format = empty($params['format']) ? self::DATE_TIME_FORMAT : $params['format'];
        return date($format, $time);
    }

    //change: array, change: http://php.net/manual/en/function.strtotime.php
    public static function getUnix(array $params = [])
    {
        $time = isset($params['time']) ? $params['time'] : time();

        if (!empty($params['change']) && is_array($params['change']))
        {
            foreach ($params['change'] as $change)
            {
                $time = strtotime($change, $time);
            }
        }

        return $time;
    }

    public static function toLocal($strDateWithZone)
    {
        return date(self::DATE_TIME_FORMAT, strtotime($strDateWithZone));
    }

    public static function toLocalDate($strDateWithZone)
    {
        return date(self::DATE_FORMAT, strtotime($strDateWithZone));
    }

    public static function getNowString($format = null)
    {
        $curDate = new \DateTime();

        if (!empty($format) && is_string($format))
        {
            return $curDate->format($format);
        }

        return $curDate->format(self::DATE_TIME_FORMAT);
    }

    public static function compareTime($oneStr, $twoStr)
    {
        $oneTime = strtotime($oneStr);
        $twoTime = strtotime($twoStr);

        if ($oneTime > $twoTime)
        {
            return 1;
        }
        else if($oneTime < $twoTime)
        {
            return -1;
        }

        return 0;
    }

    public static function compareNowTime($timeStr, $addSecond = 0)
    {
        $oneTime = strtotime($timeStr) + $addSecond;
        $twoTime = time();

        if ($oneTime > $twoTime)
        {
            return 1;
        }
        else if($oneTime < $twoTime)
        {
            return -1;
        }

        return 0;
    }
}