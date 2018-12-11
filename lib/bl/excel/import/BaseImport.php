<?php

namespace app\lib\bl\excel\import;

use app\lib\common\TmpFile;
use app\lib\common\ExcelImport;
use app\lib\bl\LogMgr;

abstract class BaseImport
{
    public $excel = null;

    //不可更改
    private $funcCallBack = null;
    private $customParams = null;
    private $key = null;

    //子类重载
    protected $minColCount = 1;
    protected $stepCount = 1;
    protected $stepMap = [];

    public function load($filePath)
    {
        $this->excel = new ExcelImport();
        $this->excel->load($filePath);
    }

    public function check(array $params = [])
    {
        if($this->excel->getColCount() < $this->minColCount)
        {
            return false;
        }

        return true;
    }

    public function getStepCount()
    {
        return $this->stepCount;
    }

    public function importInit(array $params = [])
    {
        $this->key = isset($params['key']) ? $params['key'] : null;
        $this->funcCallBack = isset($params['callBack']) ? $params['callBack'] : null;
        $this->customParams = isset($params['customParams']) ? $params['customParams'] : null;
    }

    //$params = [
    //  'companyID' = 123,              //公司编号
    //  'callBack' = null,              //执行过程回调
    //  'customParams' = null,          //自定义参数（带到回调中）
    //  'isCheck' = true                //是否进行检测数据格式（默认检测）
    //]
    //callBack 函数的标准:
    //  function name(array $customParams = [], array $progress = [])
    //      $customParams : 自定义函数，对应传入的 customParams 参数
    //      $progress : 输出的进度信息，格式符合 Enum::$PROGRESS_INFO
    abstract public function import(array $params = []);

    public function addResLog($text)
    {
        TmpFile::append($this->key, "{$text}\r\n");
    }

    public function setProgress(array $progress = [])
    {
        $progress['step_count'] = $this->stepCount;

        if (isset($progress['step']))
        {
            $step = $progress['step'];

            if (array_key_exists($step, $this->stepMap))
            {
                $progress['step_desc'] = $this->stepMap[$step];
            }
        }

        self::execCallBack($this->funcCallBack, $this->customParams, $progress);
    }

    private function execCallBack($funcCallBack = null, array $customParams = [], array $progress = [])
    {
        if (empty($funcCallBack))
        {
            return;
        }

        if (false == call_user_func_array($funcCallBack, [$customParams, $progress]))
        {
            LogMgr::addTaskImportLog(__METHOD__, LogMgr::LEVEL_ERROR, "执行回调函数失败");
        }
    }
}