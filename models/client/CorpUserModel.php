<?php

namespace app\models\client;

class CorpUserModel extends BaseModel
{
    protected static $whereFields = [
        'id', 'code', 'name', 'en_name', 'mail', 'dep_code', 'status', 'wx_id', 'line_mgr_code'
    ];

    public static function tableName()
    {
        return 't_corp_user';
    }

    public function rules()
    {
        return [
            [['code', 'name', 'wx_id'], 'required'],
            [['id', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'name', 'en_name', 'dep_code', 'wx_id'], 'string', 'max' => 50],
            [['mail', 'line_mgr_code'], 'string', 'max' => 100],
            [['code'], 'unique']
        ];
    }

    public static function saveOne(array $data)
    {
        return self::saveOneImp($data, 'wx_id');
    }
}