<?php

namespace app\models\common;

use Yii;

abstract class SysFileModel extends BaseModel
{
    protected static $whereFields = ['id', 'bl_type', 'bl_id'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_sys_file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bl_type', 'bl_id', 'name', 'path', 'size'], 'required'],
            [['id', 'bl_type', 'bl_id', 'size', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'mime_type'], 'string', 'max' => 100],
            [['ext'], 'string', 'max' => 10],
            [['path'], 'string', 'max' => 260],
        ];
    }
}