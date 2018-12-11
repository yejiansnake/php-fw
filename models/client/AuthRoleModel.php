<?php

namespace app\models\client;

use Yii;

class AuthRoleModel extends BaseModel
{
    protected static $whereFields = ['id', 'is_enable'];

    protected static $jsonFields = ['permission'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_auth_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['id','is_enable', 'created_by', 'updated_by'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['desc'], 'string', 'max' => 200],
            [['permission'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public static function getRoleWithPermission($key)
    {
        $models = self::get(['is_enable' => 1]);

        $res = [];
        foreach ($models as $model)
        {
            if (empty($model->permission))
            {
                continue;
            }

            $pms = json_decode($model->permission, true);

            if (in_array($key, $pms))
            {
                $res[] = $model->id;
            }
        }

        return $res;
    }

    public static function getPermissions(array $roleIDs)
    {
        $models = self::get(['id' => $roleIDs, 'is_enable' => 1, '@select' => ['permission']]);

        if (empty($models))
        {
            return [];
        }

        $res = [];

        foreach ($models as $model)
        {
            if (empty($model->permission))
            {
                continue;
            }

            $permissions = json_decode($model->permission, true);

            if (empty($permissions))
            {
                continue;
            }

            foreach ($permissions as $permission)
            {
                if (!in_array($permission, $res))
                {
                    $res[] = $permission;
                }
            }
        }

        return $res;
    }
}
