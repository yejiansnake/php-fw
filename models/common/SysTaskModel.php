<?php

namespace app\models\common;

use Yii;

abstract class SysTaskModel extends BaseModel
{
    protected static $whereFields = ['id', 'type', 'status'];

    protected static $jsonFields = ['content'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_sys_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'content'], 'required'],
            [['id', 'type', 'status', 'created_by', 'updated_by'], 'integer'],
            [['finished_at', 'created_at', 'updated_at'], 'safe'],
            [['content'], 'string'],
        ];
    }
}