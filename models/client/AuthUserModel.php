<?php

namespace app\models\client;

use Yii;

class AuthUserModel extends BaseModel
{
    protected static $isNoDelete = true;

    protected static $whereFields = ['id', 'name', 'phone', 'mail', 'type', 'is_enable'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_auth_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'pwd'], 'required'],
            [['type', 'is_enable', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['pwd'], 'string', 'max' => 256],
            [['name'], 'unique']
        ];
    }
}
