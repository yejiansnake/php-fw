<?php

namespace app\models\admin;

//use app\models\client\CorpUserModel;
use yii\web\ServerErrorHttpException;

class CorpAuthUserModel extends BaseModel
{
    protected static $whereFields = ['id', 'wx_id', 'is_enable', 'code', 'name', 'en_name'];
//    protected static $linkFields = ['user'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_corp_auth_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['wx_id', 'code', 'name', 'en_name'], 'required'],
            [['is_enable'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['wx_id', 'code', 'name', 'en_name'], 'string', 'max' => 50],
            [['wx_id', 'code'], 'unique']
        ];
    }

//    public function getUser()
//    {
//        return $this->hasOne(CorpUserModel::className(), ['wx_id' => 'wx_id']);
//    }
//

    public static function saveOne(array $params)
    {
        return self::saveOneImp($params, 'wx_id');
    }
}
