<?php

namespace app\models\common;

use app\lib\bl\LogMgr;
use app\lib\common\Guid;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

abstract class SysPropsModel extends BaseModel
{
    protected static $fillFields = ['created_at', 'updated_at', 'created_by', 'updated_by'];

    protected static $whereFields = ['id', 'type', 'tid', 'pid'];

    protected static $jsonFields = ['value7'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_sys_props';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'tid', 'name'], 'required'],
            [['id', 'type', 'tid', 'pid', 'value1', 'value2', 'value4', 'value5', 'created_by', 'updated_by'], 'integer'],
            [['value8', 'created_at', 'updated_at'], 'safe'],
            [['value3'], 'double'],
            [['name'], 'string', 'max' => 100],
            [['desc'], 'string', 'max' => 1000],
            [['value6', 'value7'], 'string']
        ];
    }

    public static function setProp(array $params)
    {
        if (empty($params) || empty($params['type']) || empty($params['tid']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $model = self::getOne(['type' => $params['type'], 'tid' => $params['tid']]);

        if (empty($model))
        {
            $className = get_called_class();
            $model = new $className();

            if (empty($params['name']))
            {
                $params['name'] = Guid::toString();
            }
        }
        else
        {
            if (isset($params['type']))
            {
                unset($params['type']);
            }

            if (isset($params['tid']))
            {
                unset($params['tid']);
            }

            if (isset($params['name']) && empty($params['name']))
            {
                unset($params['name']);
            }
        }

        $model->load($params);

        if (!$model->save())
        {
            LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR,
                "set prop failed, info: {$model->getErrorString()}");

            throw new ServerErrorHttpException("entity save failed");
        }

        return $model;
    }

    public static function getProp($type, $tid)
    {
        return self::getOne(['type' => $type, 'tid' => $tid]);
    }

    public static function getProps($type)
    {
        return self::get(['type' => $type]);
    }

    public static function getPropByPid($type, $pid)
    {
        return self::getOne(['type' => $type, 'pid' => $pid]);
    }
}
