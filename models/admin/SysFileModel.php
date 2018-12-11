<?php

namespace app\models\admin;

class SysFileModel extends \app\models\common\SysFileModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}