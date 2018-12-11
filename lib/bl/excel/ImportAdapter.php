<?php
/**
 * EXCEL数据导入适配器
 * User: yejian
 * Date: 2016/5/18
 * Time: 10:51
 */

namespace app\lib\bl\excel;

use app\lib\bl\Enum;
use app\lib\bl\excel\import\TestImport;

class ImportAdapter
{
    private $impObj = null;

    const TYPE_MIN = 1;         //最小值
    const TYPE_MAX = 100;         //最大值

    const TYPE_CASE = 1;        //


    private static $IMPORT_CLASS_MAP = [
        self::TYPE_CASE => 'Case',
    ];

    public function __construct($type)
    {
        if ($type < self::TYPE_MIN || $type > self::TYPE_MAX)
        {
            throw new \Exception("not support type:{$type}");
        }

        if (!array_key_exists($type, self::$IMPORT_CLASS_MAP))
        {
            throw new \Exception("not support type:{$type}");
        }

        $className = 'app\lib\bl\excel\import\\' . self::$IMPORT_CLASS_MAP[$type] . 'Import';
        $this->impObj = new $className();
    }

    public function load($filePath)
    {
        try
        {
            $this->impObj->load($filePath);
        }
        catch (\Exception $ex)
        {
            return false;
        }

        return true;
    }

    public function check(array $params = [])
    {
        return $this->impObj->check($params);
    }

    public function getStepCount()
    {
        return $this->impObj->getStepCount();
    }

    public function getRowCount()
    {
        return $this->impObj->getRowCount();
    }

    public function import(array $params = [])
    {
        $needCheck = true;
        if (isset($params['isCheck']))
        {
            if (empty($params['isCheck']))
            {
                $needCheck = false;
            }
        }

        if ($needCheck)
        {
            if (!$this->check())
            {
                throw new \Exception('check failed');
            }
        }

        $this->impObj->importInit($params);
        $this->impObj->import($params);
    }
}