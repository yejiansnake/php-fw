<?php

namespace app\models\admin;

class SysPropsModel extends \app\models\common\SysPropsModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}