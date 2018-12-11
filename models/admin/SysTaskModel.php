<?php

namespace app\models\admin;

class SysTaskModel extends \app\models\common\SysTaskModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}