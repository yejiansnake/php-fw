<?php

namespace app\models\client;

use Yii;
use yii\web\ServerErrorHttpException;

class CorpDepModel extends BaseModel
{
    protected static $whereFields = ['id', 'code', 'name'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_corp_dep';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['code', 'name'], 'string', 'max' => 50],
            [['code'], 'unique']
        ];
    }

    public static function saveOne(array $data)
    {
        return self::saveOneImp($data, 'code');
    }
}