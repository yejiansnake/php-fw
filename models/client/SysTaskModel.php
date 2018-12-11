<?php

namespace app\models\client;

class SysTaskModel extends \app\models\common\SysTaskModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}