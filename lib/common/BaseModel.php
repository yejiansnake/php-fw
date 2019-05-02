<?php
/**
 * 基础数据模型
 * User: yejian
 * Date: 2016/3/11
 * Time: 17:39
 */

namespace app\lib\common;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\BadRequestHttpException;
use app\lib\common\session\BaseCurUserSession;
use yii\web\ServerErrorHttpException;

abstract class BaseModel extends ActiveRecord
{
    //load 方法允许填充的字段策略
    const FILL_TYPE_DENY = 0;
    const FILL_TYPE_ALLOW = 1;
    protected static $fillType = self::FILL_TYPE_DENY;
    protected static $fillFields = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'];

    //原生 YII and where 查询条件启用
    public static $enableCondition = false;

    //不输出的字段
    public static $hiddenFields = [];
    //如果数据表有 deleted_at 字段作为删除状态的，打开该功能后 model->delete 将会自动切换为修改字段值代替
    protected static $isNoDelete = false;

    //提供查询的字段
    protected static $whereFields = [];

    //提供字符串转换成数组的查询的字段
    protected static $whereExFields = [];

    //模糊查询方式字段
    protected static $whereLikeFields = [];

    //范围查询方式字段
    protected static $whereRangeFields = [];
    //非结构化数据字段，框架自动添加 field, load 函数的逻辑处理
    protected static $jsonFields = [];

    //关联表数据自动在 fields 输出，名称需要对应关联的 getXXX 方法
    protected static $linkFields = [];

    const AGGREGATION_COUNT = 1;
    const AGGREGATION_MAX = 2;
    const AGGREGATION_MIN = 3;
    const AGGREGATION_SUM = 4;
    const AGGREGATION_AVG = 5;

    protected static $dbConfig = [];

    public static function setDb($dbConfig)
    {
        static::$dbConfig = $dbConfig;
    }

    public static function getDb()
    {
        if (!empty(static::$dbConfig))
        {
            return DbConnectionMgr::getDbConn(static::$dbConfig);
        }

        return static::getDefaultDb();
    }

    public static function getDefaultDb()
    {
        return null;
    }

    public static function warpQueryAndWhereMap(&$query, array $params, array $keyFieldMap, $isAddCurTable = true)
    {
        if (empty($query) || empty($params) || empty($keyFieldMap))
        {
            return $query;
        }

        foreach ($keyFieldMap as $key => $fieldName)
        {
            self::warpQueryAndWhere($query, $params, $key, $fieldName, $isAddCurTable);
        }

        return $query;
    }

    public static function warpQueryAndWhereArray(&$query, array $params, array $fieldNameArray, $isAddCurTable = true)
    {
        if (empty($query) || empty($params) || empty($fieldNameArray))
        {
            return $query;
        }

        foreach ($fieldNameArray as $fieldName)
        {
            self::warpQueryAndWhere($query, $params, $fieldName, $fieldName, $isAddCurTable);
        }

        return $query;
    }

    public static function warpQueryAndWhereArrayEx(&$query, array $params, array $fieldNameArray, $isAddCurTable = true)
    {
        if (empty($query) || empty($params) || empty($fieldNameArray))
        {
            return $query;
        }

        foreach ($fieldNameArray as $key => $fieldName)
        {
            if (array_key_exists($key, $params))
            {
                $params[$fieldName] = explode(',', $params[$key]);

                self::warpQueryAndWhere($query, $params, $fieldName, $fieldName, $isAddCurTable);
            }
        }

        return $query;
    }

    public static function warpQueryAndWhere(&$query, array $params, $name, $fieldName = '', $isAddCurTable = true)
    {
        if (empty($query) || empty($params) || empty($name))
        {
            return $query;
        }

        if ($query instanceof ActiveQuery)
        {
            if (empty($fieldName))
            {
                $fieldName = $name;
            }

            if (isset($params[$name]))
            {
                $temp = $params[$name];

                $fullFieldName = $fieldName;

                if ($isAddCurTable)
                {
                    $fullFieldName = static::tableName() . ".{$fieldName}";
                }

                $query->andWhere([$fullFieldName => $temp]);
            }
        }

        return $query;
    }

    public static function warpQueryAndWhereOpt(
        &$query,
        array $params,
        $name,
        $opt,
        $fieldName = '',
        $isAddCurTable = true)
    {
        if (empty($query) || empty($params) || empty($name) || empty($opt))
        {
            return $query;
        }

        if ($query instanceof ActiveQuery)
        {
            if (empty($fieldName))
            {
                $fieldName = $name;
            }

            if (isset($params[$name]))
            {
                $temp = $params[$name];

                $fullFieldName = $fieldName;

                if ($isAddCurTable)
                {
                    $fullFieldName = static::tableName() . ".{$fieldName}";
                }

                $query->andWhere([$opt, $fullFieldName, $temp]);
            }
        }

        return $query;
    }

    public static function warpQueryAndWhereLike(
    &$query, array $params, $name,
    $fieldName = '', $isAddCurTable = true)
{
    return self::warpQueryAndWhereOpt($query, $params, $name, 'LIKE', $fieldName, $isAddCurTable);
}

    public static function warpQueryAndWhereLikeArray(&$query, array $params, array $fieldNameMap, $isAddCurTable = true)
    {
        if (empty($fieldNameMap) || !is_array($fieldNameMap))
        {
            return $query;
        }

        foreach ($fieldNameMap as $name => $fieldName)
        {
            self::warpQueryAndWhereLike($query, $params, $name, $fieldName, $isAddCurTable);
        }

        return $query;
    }

    public static function warpQueryAndWhereRangeArray(&$query, array $params, array $fieldNameMap, $isAddCurTable = true)
    {
        if (empty($fieldNameMap) || !is_array($fieldNameMap))
        {
            return $query;
        }

        foreach ($fieldNameMap as $name => $item)
        {
            if (empty($item[0]) || empty($item[1]))
            {
                continue;
            }

            self::warpQueryAndWhereOpt($query, $params, $name, $item[1], $item[0], $isAddCurTable);
        }

        return $query;
    }

    public function beforeSave($insert)
    {
        if (defined('YII_CONSOLE'))
        {
            return parent::beforeSave($insert);
        }

        $userInfo = BaseCurUserSession::get();
        if (!empty($userInfo) && !empty($userInfo['id']))
        {
            if ($insert && $this->hasAttribute('created_by'))
            {
                $this->created_by = $userInfo['id'];
            }

            if ($this->hasAttribute('updated_by'))
            {
                $this->updated_by = $userInfo['id'];
            }
        }

        return parent::beforeSave($insert);
    }

    public function fields()
    {
        $fields = parent::fields();

        if (isset($fields['deleted_at']))
        {
            unset($fields['deleted_at']);
        }

        if (isset($fields['pwd']))
        {
            unset($fields['pwd']);
        }

        if (!empty(static::$jsonFields))
        {
            foreach (static::$jsonFields as $jsonField)
            {
                if (!empty($jsonField))
                {
                    $fields[$jsonField] = function ($thisObj, $field)
                    {
                        if (empty($thisObj->$field))
                        {
                            return [];
                        }

                        return json_decode($thisObj->$field, true);
                    };
                }
            }
        }

        if (!empty(static::$linkFields))
        {
            foreach (static::$linkFields as $linkField)
            {
                if (!empty($linkField))
                {
                    $fields[$linkField] = function ($thisObj, $field)
                    {
                        return $thisObj->$field;
                    };
                }
            }
        }

        if (!empty(static::$hiddenFields))
        {
            foreach (static::$hiddenFields as $unsetField)
            {
                if (!empty($unsetField))
                {
                    unset($fields[$unsetField]);
                }
            }
        }

        return $fields;
    }

    public function load($data, $formName = null)
    {
        $notKeys = [];
        $sameKeys = [];

        foreach ($data as $key => $value)
        {
            if (in_array($key, static::$fillFields))
            {
                $sameKeys[] = $key;
            }
            else
            {
                $notKeys[] = $key;
            }
        }

        if (static::$fillType == self::FILL_TYPE_ALLOW)
        {
            foreach ($notKeys as $key)
            {
                unset($data[$key]);
            }
        }
        else
        {
            foreach ($sameKeys as $key)
            {
                unset($data[$key]);
            }
        }

        if (isset($data['deleted_at']))
        {
            unset($data['deleted_at']);
        }

        if (isset($data['pwd']))
        {
            $data['pwd'] = PwdCreator::get($data['pwd']);
        }

        if (!empty(static::$jsonFields))
        {
            foreach (static::$jsonFields as $jsonField)
            {
                if (!empty($jsonField) && isset($data[$jsonField]))
                {
                    $data[$jsonField] = json_encode($data[$jsonField], JSON_UNESCAPED_UNICODE);
                }
            }
        }

        return parent::load($data, isset($formName) ?  $formName : '');
    }

    public function resetDateTime(array &$data, $key)
    {
        if (!isset($key))
        {
            return;
        }

        if (isset($data[$key]))
        {
            $data[$key] = date("Y-m-d H:i:s", strtotime($data[$key]));
        }
    }

    public static function addQueryWhere(&$query, array $params, array $fields)
    {
        foreach ($fields as $key)
        {
            if (isset($params[$key]))
            {
                $query->andWhere([static::tableName() . ".$key" => $params[$key]]);
            }
        }
    }

    //可继承覆盖
    public static function getQuery($params = [])
    {
        $query = parent::find();

        if (!empty(static::$whereFields))
        {
            self::warpQueryAndWhereArray($query, $params, static::$whereFields);
        }

        if (!empty(static::$whereExFields))
        {
            self::warpQueryAndWhereArrayEx($query, $params, static::$whereExFields);
        }

        if (!empty(static::$whereLikeFields))
        {
            self::warpQueryAndWhereLikeArray($query, $params, static::$whereLikeFields);
        }

        if (!empty(static::$whereRangeFields))
        {
            self::warpQueryAndWhereRangeArray($query, $params, static::$whereRangeFields);
        }

        if (static::$enableCondition
            && !empty($params['@conditionArray'])
            && is_array($params['@conditionArray'])
        )
        {
            foreach ($params['@conditionArray'] as $condition)
            {
                $query->andWhere($condition);
            }
        }

        if (static::$isNoDelete && empty($params['@delete']))
        {
            $query->andWhere([static::tableName() . '.deleted_at' => null]);
        }

        if (isset($params['pwd']))
        {
            $query->andWhere(['pwd' => PwdCreator::get($params['pwd'])]);
        }

        if (isset($params['@select']))
        {
            $selectFields = [];
            foreach ($params['@select'] as $selectField)
            {
                $selectFields[] = static::tableName() . ".{$selectField}";
            }

            $query->select($selectFields);
        }

        if (isset($params['@orderBy']))
        {
            $query->orderBy($params['@orderBy']);
        }

        if (isset($params['@limit']))
        {
            $query->limit($params['@limit']);
        }

        if (isset($params['@offset']))
        {
            $query->offset($params['@offset']);
        }

        return $query;
    }

    public static function getOne($params = [])
    {
        return self::get($params, true);
    }

    public static function getCount($params = [])
    {
        return self::get($params, true, ['type' => self::AGGREGATION_COUNT]);
    }

    public static function getMax($filedName, $params = [])
    {
        return self::get($params, true, ['type' => self::AGGREGATION_MAX, 'value' => $filedName]);
    }

    public static function getMin($filedName, $params = [])
    {
        return self::get($params, true, ['type' => self::AGGREGATION_MIN, 'value' => $filedName]);
    }

    public static function getSum($filedName, $params = [])
    {
        return self::get($params, true, ['type' => self::AGGREGATION_SUM, 'value' => $filedName]);
    }

    public static function getAvg($filedName, $params = [])
    {
        return self::get($params, true, ['type' => self::AGGREGATION_AVG, 'value' => $filedName]);
    }

    //$aggregation 聚合类型
    public static function get($params = [], $isOne = false, $aggregation = null)
    {
        $query = static::getQuery($params);

        if (isset($params['@asArray']))
        {
            $query->asArray();
        }

        if ($isOne)
        {
            if (isset($aggregation) && isset($aggregation['type']))
            {
                switch ($aggregation['type'])
                {
                    case self::AGGREGATION_COUNT:
                    {
                        if (isset($aggregation['value']))
                        {
                            return (int)$query->count($aggregation['value']);
                        }
                        else
                        {
                            return (int)$query->count();
                        }
                    }
                        break;
                    case self::AGGREGATION_MAX:
                    {
                        if (!isset($aggregation['value']))
                        {
                            throw new BadRequestHttpException('params error');
                        }

                        return $query->max($aggregation['value']);
                    }
                        break;
                    case self::AGGREGATION_MIN:
                    {
                        if (!isset($aggregation['value']))
                        {
                            throw new BadRequestHttpException('params error');
                        }

                        return $query->min($aggregation['value']);
                    }
                        break;
                    case self::AGGREGATION_SUM:
                        {
                            if (!isset($aggregation['value']))
                            {
                                throw new BadRequestHttpException('params error');
                            }

                            return $query->sum($aggregation['value']);
                        }
                        break;
                    case self::AGGREGATION_AVG:
                        {
                            if (!isset($aggregation['value']))
                            {
                                throw new BadRequestHttpException('params error');
                            }

                            return $query->average($aggregation['value']);
                        }
                        break;
                    default:
                    {
                        throw new BadRequestHttpException('params error');
                    }
                }
            }

            return $query->one();
        }

        return $query->all();
    }

    public function delete()
    {
        if (static::$isNoDelete)
        {
            $this->deleted_at = DateTimeEx::getString();

            if (!$this->save())
            {
                return false;
            }

            return true;
        }

        return parent::delete();
    }

    public static function deleteAll($condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();

        if (static::$isNoDelete)
        {
            $command->update(static::tableName(), ['deleted_at' => DateTimeEx::getString()], $condition, $params);
        }
        else
        {
            $command->delete(static::tableName(), $condition, $params);
        }

        return $command->execute();
    }

    public static function execSql($sql, array &$params, $paramPrefix = null, $returnRow = false)
    {
        $strParams = '';
        $realParams = [];

        if (!empty($params))
        {
            foreach ($params as $key => $value)
            {
                $paramKey = ':' . (empty($paramPrefix) ? $key : ($paramPrefix . $key));
                $realParams[$paramKey] = $value;
                $strParams .= empty($strParams) ? $paramKey : (',' . $paramKey);
            }
        }

        $cmd = static::getDb()->createCommand($sql, $realParams);

        $res = null;

        try
        {
            $res = empty($returnRow) ? $cmd->execute() : $cmd->queryAll();
        }
        catch (\Exception $ex)
        {
            LogHelper::common(__METHOD__, LogHelper::LEVEL_ERROR,
                "call {$sql} failed, info:{$ex->getMessage()}");
            return null;
        }

        return $res;
    }

    public static function callProcedureEx($procName, array &$params)
    {
        return self::callProcedure($procName, $params, 'p_', true);
    }

    public static function callProcedure($procName, array &$params, $paramPrefix = null, $returnOneRow = false)
    {
        $strParams = '';
        $realParams = [];

        if (!empty($params))
        {
            foreach ($params as $key => $value)
            {
                $paramKey = ':' . (empty($paramPrefix) ? $key : ($paramPrefix . $key));
                $realParams[$paramKey] = $value;
                $strParams .= empty($strParams) ? $paramKey : (',' . $paramKey);
            }
        }

        $cmd = static::getDb()->createCommand("CALL {$procName}({$strParams})", $realParams);

        $res = null;

        try
        {
            $res = empty($returnOneRow) ? $cmd->execute() : $cmd->queryOne();
        }
        catch (\Exception $ex)
        {
            LogHelper::common(__METHOD__, LogHelper::LEVEL_ERROR,
                "call {$procName} failed, info:{$ex->getMessage()}");
            return null;
        }

        return $res;
    }

    public function getErrorString()
    {
        if (!$this->hasErrors())
        {
            return '';
        }

        return json_encode($this->firstErrors, JSON_UNESCAPED_UNICODE);
    }

    //获取数据库JSON字段的对象数据
    public static function getJsonFieldValue($jsonFieldValue)
    {
        if (empty($jsonFieldValue))
        {
            return [];
        }

        return json_decode($jsonFieldValue, true);
    }

    public static function saveOne(array $data)
    {
        return self::saveOneImp($data);
    }

    public static function saveOneImp(array $data, $keyFields = 'id', &$outNew = null)
    {
        if (empty($data))
        {
            throw new ServerErrorHttpException("params invalid");
        }

        $model = null;

        $checkExist = false;
        $getParams = [];

        if (!empty($keyFields))
        {
            if (is_string($keyFields))
            {
                $keyFields = [$keyFields];
            }

            foreach ($keyFields as $field)
            {
                if (array_key_exists($field, $data))
                {
                    $getParams[$field] = $data[$field];
                }
            }
            if (count($getParams) == count($keyFields))
            {
                $checkExist = true;
            }
        }

        if ($checkExist && !empty($getParams))
        {
            $model = self::getOne($getParams);
        }

        if (empty($model))
        {
            $className = get_called_class();
            $model = new $className();

            if (isset($outNew))
            {
                $outNew = true;
            }
        }
        else
        {
            foreach ($keyFields as $field)
            {
                if (array_key_exists($field, $data))
                {
                    unset($data[$field]);
                }
            }
        }

        $model->load($data);
        if (!$model->save())
        {
            $msg = "save user failed, msg:{$model->getErrorString()}";
            LogHelper::common(__METHOD__, LogHelper::LEVEL_ERROR, $msg);
            throw new ServerErrorHttpException($msg);
        }

        return $model;
    }

    public static function deleteOne($id)
    {
        return self::deleteOneImp(['id' => $id]);
    }

    public static function deleteOneImp(array $params)
    {
        if (empty($params))
        {
            return null;
        }

        $model = self::getOne($params);

        if (empty($model))
        {
            return null;
        }

        $model->delete();

        return $model;
    }
}
