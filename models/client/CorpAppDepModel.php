<?php

namespace app\models\client;

use Yii;
use app\lib\bl\LogMgr;

class CorpAppDepModel extends BaseModel
{
    protected static $isNoDelete = true;

    protected static $whereFields = ['id', 'parent_id', 'name'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_corp_app_dep';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name'], 'required'],
            [['id', 'parent_id', 'order'], 'integer'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name'], 'string', 'max' => 100]
        ];
    }

    public static function deleteOne($id)
    {
        $model =  self::deleteOne($id);

        if (!empty($model))
        {
            CorpAppUserDepMapModel::deleteByDepID($model->id);
        }
    }

    public static function saveOneFromWxEvent(array $data)
    {
        $saveParams = [
            'id' => $data['Id'],
            'name' => $data['Name'],
            'parent_id' => $data['ParentId'],
        ];

        if (isset($data['Order']))
        {
            $saveParams['order'] = $data['Order'];
        }

        self::saveOne($saveParams);
    }

    public static function saveOne(array $data)
    {
        return self::saveOneImp($data, 'id');
    }
}
