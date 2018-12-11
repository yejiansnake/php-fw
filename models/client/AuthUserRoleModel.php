<?php

namespace app\models\client;

use Yii;

class AuthUserRoleModel extends BaseModel
{
    protected static $whereFields = ['id', 'user_id', 'role_id'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_auth_user_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'required'],
            [['id', 'user_id', 'role_id', 'created_by'], 'integer'],
            [['created_at'], 'safe'],
            [['user_id', 'role_id'], 'unique', 'targetAttribute' => ['user_id', 'role_id']],
        ];
    }

    public static function getUserRoles($userID)
    {
        $models = self::get(['user_id' => $userID, '@select' => ['role_id']]);

        $res = [];
        foreach ($models as $model)
        {
            $res[] = $model->role_id;
        }

        return $res;
    }
}
