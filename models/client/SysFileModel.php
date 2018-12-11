<?php

namespace app\models\client;

class SysFileModel extends \app\models\common\SysFileModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}