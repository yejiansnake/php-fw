<?php

namespace app\models\client;

use Yii;

class AuthUserResourceModel extends BaseModel
{
    const TYPE_EXAM = 100;    //问卷资源

    protected static $whereFields = ['id', 'user_id', 'type'];

    protected static $jsonFields = ['resource'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_auth_user_resource';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'resource'], 'required'],
            [['id', 'user_id', 'type', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['resource'], 'string'],
            [['user_id', 'type'], 'unique', 'targetAttribute' => ['user_id', 'type']],
        ];
    }

    public static function getUserResourceMap($userID)
    {
        $models = self::get(['user_id' => $userID, '@select' => ['type', 'resource']]);

        $res = [];
        foreach ($models as $model)
        {
            if (!array_key_exists($model->type, $res))
            {
                $resource = [];
                if (!empty($model->resource))
                {
                    $resource = json_decode($model->resource, true);
                }

                $res[$model->type] = $resource;
            }
        }

        return $res;
    }
}
