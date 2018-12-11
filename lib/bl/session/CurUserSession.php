<?php
/**
 * 当前用户Seesion
 */

namespace app\lib\bl\session;

use app\lib\common\session\BaseCurUserSession;

final class CurUserSession extends BaseCurUserSession
{
    public static function getRelationIDs($relationType)
    {
        $value = parent::get();

        if (!isset($value))
        {
            return [0];
        }

        if (!isset($value['relation']))
        {
            return [0];
        }

        if (!isset($value['relation'][$relationType]))
        {
            return [0];
        }

        $resArray = array_keys($value['relation'][$relationType]);

        $resArray[] = 0;

        return $resArray;
    }
}