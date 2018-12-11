<?php

namespace app\models\client;

class AppUserModel extends BaseModel
{
    protected static $isNoDelete = true;
    protected static $jsonFields = ['extra'];
    protected static $whereFields = ['id', 'open_id', 'nick_name'];

    public static function tableName()
    {
        return 't_app_user';
    }

    public function rules()
    {
        return [
            [['open_id'], 'required'],
            [['id','gender', 'subscribe'], 'integer'],
            [['extra'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['open_id', 'nick_name', 'city', 'province', 'country'], 'string', 'max' => 100],
            [['head_img'], 'string', 'max' => 260],
            [['open_id'], 'unique']
        ];
    }

    public function load($data, $formName = null)
    {
        if (!empty($data['nick_name']))
        {
            $data['nick_name'] = trim($data['nick_name']);
        }

        return parent::load($data, $formName);
    }

    public static function saveOneFromWxChat(array $params)
    {
        $headImg = '';
        if (!empty($params['headimgurl']))   //转换成 https
        {
            $headImg = str_replace('http://', 'https://', $params['headimgurl']);
            $headImg = substr($headImg, 0, strlen($headImg) - 1) . '132';
        }

        return self::saveOne([
            'open_id' => $params['openid'],
            'nick_name' => empty($params['nickname']) ? '' :$params['nickname'] ,
            'gender' => empty($params['sex']) ? 0 : $params['sex'],
            'head_img' => $headImg,
            'city' => empty($params['city']) ? '' :$params['city'] ,
            'province' => empty($params['province']) ? '' :$params['province'],
            'country' => empty($params['country']) ? '' :$params['country'],
            'subscribe' => empty($params['subscribe']) ? 0 : 1,
            'extra' => $params,
        ]);
    }

    public static function saveOne(array $data)
    {
        $isNew = false;
        $model = self::saveOneImp($data, 'open_id', $isNew);
        $res = $model->toArray();
        $res['isNew'] = $isNew;
        return $res;
    }
}