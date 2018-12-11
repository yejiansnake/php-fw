<?php

namespace app\models\client;

use Yii;
use app\lib\common\ArrayUtility;

class CorpAppUserModel extends BaseModel
{
    protected static $isNoDelete = true;

    protected static $jsonFields = ['dep', 'full'];

    protected static $whereFields = ['id', 'user_id', 'name', 'gender', 'phone', 'status'];

    public static $hiddenFields = ['full'];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_corp_app_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name'], 'required'],
            [['id', 'gender', 'is_leader', 'status'], 'integer'],
            [['dep', 'full'], 'string'],
            [['created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['user_id', 'name', 'en_name', 'position', 'phone', 'telephone', 'email'], 'string', 'max' => 100],
            [['avatar'], 'string', 'max' => 260],
            [['user_id'], 'unique']
        ];
    }

    public function load($data, $formName = null)
    {
        if (!empty($data['name']))
        {
            $data['name'] = trim($data['name']);
        }

        return parent::load($data, $formName);
    }

    public static function deleteOne($id)
    {
        return self::deleteOneImp(['user_id' => $id]);
    }

    public static function saveOneFromWxLogin(array $params)
    {
        return self::saveOneFromApi($params);
    }

    public static function saveOneFromApi(array $data)
    {
        $saveParams = ArrayUtility::getFieldArray($data, [
            'userid' => 'user_id',
            'name' => null,
            'english_name' => 'en_name',
            'gender' => null,
            'position' => null,
            'mobile' => 'phone',
            'telephone' => null,
            'email' => null,
            'department' => 'dep',
            'isleader' => 'is_leader',
            'avatar' => null,
            'status' => null,
        ]);

        $saveParams['full'] = $data;

        return self::saveOne($saveParams);
    }

    public static function saveOneFromWxEvent(array $data)
    {
        $saveParams = ArrayUtility::getFieldArray($data, [
            'UserID' => 'user_id',
            'NewUserID' => 'new_user_id',
            'Name' => 'name',
            'EnglishName' => 'en_name',
            'Gender' => 'gender',
            'Position' => 'position',
            'Mobile' => 'phone',
            'Telephone' => 'telephone',
            'Email' => 'email',
            'Department' => 'dep',
            'IsLeader' => 'is_leader',
            'Avatar' => 'avatar',
            'Status' => 'status',
        ]);

        $saveParams['full'] = $data;

        return self::saveOne($saveParams);
    }

    public static function saveOne(array $data)
    {
        if (empty($data))
        {
            throw new \Exception('params invalid');
        }

        if (!empty($data['avatar']))   //转换成 https
        {
            $avatar = str_replace('http://', 'https://', $data['avatar']);
            $data['avatar'] = $avatar;
        }

        $model = self::findOne(['user_id' => $data['user_id']]);

        $sourceDep = '';

        if (empty($model))
        {
            $model = new self();
        }
        else
        {
            $sourceDep = empty($model->dep) ? '' : $model->dep;

            if (empty($data['new_user_id']))
            {
                unset($data['user_id']);
            }
            else
            {
                $data['user_id'] = $data['new_user_id'];
                unset($data['new_user_id']);
            }

            $model->deleted_at = null;
        }


        $model->load($data);

        if (!$model->save())
        {
            $error = json_encode($model->firstErrors, JSON_UNESCAPED_UNICODE);
            throw new \Exception("save user failed, info:{$error}");
        }

        if (isset($data['dep']))
        {
            self::saveUserDep($model->id, $data['dep'], $sourceDep);
        }

        return $model;
    }

    private static function saveUserDep($userID, $dep, $sourceDep = '')
    {
        $depArray = [];
        if (!is_array($dep))
        {
            $depArray = json_decode($dep, true);
        }
        else
        {
            $depArray = $dep;
        }

        $sourceDep = json_decode($sourceDep, true);

        if (!empty($sourceDep))
        {
            //清理（有旧的没有新的）
            if (empty($depArray))
            {
                CorpAppUserDepMapModel::deleteByUserID($userID);
                return;
            }

            $diffArray = array_diff($depArray, $sourceDep);

            //相同就不任何改变
            if (empty($diffArray))
            {
                return;
            }

            //更新的时候先清理
            CorpAppUserDepMapModel::deleteByUserID($userID);
        }

        foreach ($depArray as $depID)
        {
            CorpAppUserDepMapModel::saveOne([
                'user_id' => $userID,
                'dep_id' => $depID
            ]);
        }
    }
}