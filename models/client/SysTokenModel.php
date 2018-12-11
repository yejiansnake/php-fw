<?php

namespace app\models\client;

class SysTokenModel extends \app\models\common\SysTokenModel
{
    public static function getDefaultDb()
    {
        return BaseModel::getDefaultDb();
    }
}