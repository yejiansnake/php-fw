<?php

namespace app\models\common;

use Yii;
use app\lib\common\Guid;

abstract class SysTokenModel extends BaseModel
{
    protected static $jsonFields = ['extra'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_sys_token';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value', 'expires_in', 'expires_at'], 'required'],
            [['type', 'is_enable', 'expires_in', 'created_by', 'updated_by'], 'integer'],
            [['expires_at', 'created_at', 'updated_at'], 'safe'],
            [['value'], 'string', 'max' => 128],
            [['extra'], 'string'],
            [['value'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '编号',
            'value' => '令牌信息',
            'extra' => '附带的信息，根据不用的业务，自己定义数据内容规则',
            'type' => '类型',
            'is_enable' => '是否有效, 0:无效; 1:有效;
                1.可以根据目需要手动设置为无效，这样就可以迫使令牌失效。
                2.当token已经被使用后，也可以设置为无效，或者直接删除该token信息。',
            'expires_in' => '过期的分钟',
            'expires_at' => '过期时间',
            'created_at' => '创建时间',
            'updated_at' => '最后修改时间',
            'created_by' => '创建人',
            'updated_by' => '最后修改人',
        ];
    }

    public static function getQuery($params = [])
    {
        $query = parent::getQuery($params);

        self::warpQueryAndWhereArray($query, $params,
            ['id', 'value', 'type', 'is_enable']);

        return $query;
    }

    /*
     * 获取申请重置密码的令牌
     * @param $user_id 用户编号(与令牌绑定)
     * @param $opt_user_id 操作人的用户编号
     * @return string 令牌信息
     */
    public static function createToken($opt_user_id, $type, $prefix, $expires_in, $extra = [])
    {
        $curTime = new \DateTime();
        $curTime->add(new \DateInterval("P{$expires_in}S"));

        //1.生成唯一标识码
        $token = "{$prefix}_" . Guid::ToStringWeb();

        //2.token 写入数据库，并且将用户相关信息写入extra，为下次使用时备用
        $className = get_called_class();
        $model = new $className();
        $model->load([
            'value' => $token,
            'extra' => $extra,
            'type' => $type,
            'is_enable' => 1,
            'expires_in' => $expires_in,
            'expires_at' => $curTime->format('Y-m-d H:i:s'),
            'created_by' => $opt_user_id,
            'updated_by' => $opt_user_id,
        ]);

        if (!$model->save())
        {
            $error = json_encode($model->firstErrors, JSON_UNESCAPED_UNICODE);
            throw new \Exception("wx app pre auth code to token save failed, info:{$error}");
        }

        return $token;
    }

    /*
     * 验证令牌是否有效
     * @param $token 令牌绑定
     * @param $isSetDisable 验证成功后是否立刻失效
     * @return 额外数据,如果错误则返回 null
     */
    public static function authToken($token, $isSetDisable = false)
    {
        $curTime = new \DateTime();

        $model = self::find()->where([
            'value' => $token,
            'is_enable' => 1,
        ])->andWhere(['>', 'expires_at', $curTime->format('Y-m-d H:i:s')])->one();

        if (!isset($model)) {
            return null;
        }

        $extra = json_decode($model->extra, true);

        if ($isSetDisable)
        {
            $model->is_enable = 0;
            $model->save();
        }

        return $extra;
    }
}