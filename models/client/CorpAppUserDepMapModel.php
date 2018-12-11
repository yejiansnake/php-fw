<?php

namespace app\models\client;

use Yii;
use app\lib\bl\LogMgr;

class CorpAppUserDepMapModel extends BaseModel
{
    protected static $whereFields = ['user_id', 'dep_id'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_corp_user_dep_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'dep_id'], 'required'],
            [['user_id', 'dep_id'], 'integer'],
            [['created_at'], 'safe']
        ];
    }

    public static function saveOne(array $data)
    {
        return self::saveOneImp($data, ['user_id', 'dep_id']);
    }

    public static function deleteByUserID($userID)
    {
        parent::deleteAll(['user_id' => $userID]);
    }

    public static function deleteByDepID($depID)
    {
        parent::deleteAll(['dep_id' => $depID]);
    }
}
