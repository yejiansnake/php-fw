<?php

namespace app\models\admin;

class SysTokenModel extends \app\models\common\SysTokenModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}