<?php

namespace app\models\client;

class SysPropsModel extends \app\models\common\SysPropsModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}