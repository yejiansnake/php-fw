<?php

namespace app\models\client;

abstract class BaseModel extends \app\lib\common\BaseModel
{
    public static function getDefaultDb()
    {
        return \Yii::$app->db_client;
    }
}
