<?php
/**
 * 导入员工数据
 * User: Administrator
 * Date: 2016/5/19
 * Time: 10:22
 */

namespace app\lib\bl\excel\import;

use app\lib\bl\LogMgr;
use app\lib\bl\ProgressHelper;
use app\models\mgr\TaskCaseModel;
use app\models\mgr\TaskModel;
use yii\web\ServerErrorHttpException;

class CaseImport extends BaseImport
{
    protected $minColCount = 2;

    const COL_NAME = 0;         //姓名
    const COL_ACTION = 1;       //内容

    protected $stepCount = 1;
    const STEP_SAVE_DATA = 1;

    protected $stepMap = [
        self::STEP_SAVE_DATA => '加载数据',
    ];

    public function import(array $params = [])
    {
        $taskID = $params['extParam'];
        $model = TaskModel::getOne(['id' => $taskID]);
        if (empty($model))
        {
            $this->setProgress([
                'code' => ProgressHelper::PROGRESS_CODE_FAILED,
                'msg' => '任务参数错误',
            ]);

            return false;
        }

        LogMgr::addTaskImportLog(__METHOD__, LogMgr::LEVEL_INFO,
            "数据行数:{$this->excel->getRowCount()}, 最大列索引:{$this->excel->getColCount()}");
        $dataTable = $this->excel->getAll(['ignoreOne' => true, 'colCount' => $this->minColCount]);
        $totalCount = count($dataTable);
        $this->setProgress([
            'step' => self::STEP_SAVE_DATA,
            'finish' => 0,
            'total_count' => $totalCount
        ]);

        LogMgr::addTaskImportLog(__METHOD__, LogMgr::LEVEL_INFO, "导入数据行数:" . count($dataTable));

        //------------------------------------------------------------------------------------------
        //写入数据

        $finishCount = 0;
        $failedCount = 0;
        $addCount = 0;
        foreach ($dataTable as $dataRow)
        {
            $res = $this->saveDataRow($model, $dataRow, $finishCount + 2);
            $addCount += $res->add;
            $failedCount += $res->failed;
            $finishCount++;
            if ($finishCount % 10 == 0)
            {
                $successCount = $addCount;
                $msg = "数据{$totalCount}行，成功导入{$successCount}行，其中新增{$addCount}行，失败{$failedCount}行";
                $this->setProgress([
                    'msg' => $msg,
                    'step' => self::STEP_SAVE_DATA,
                    'finish' => $finishCount,
                    'total_count' => $totalCount
                ]);
            }
        }

        $successCount = $addCount;
        $msg = "数据{$totalCount}行，成功导入{$successCount}行，其中新增{$addCount}行，失败{$failedCount}行";
        LogMgr::addTaskImportLog(__METHOD__, LogMgr::LEVEL_INFO, $msg);
        $this->setProgress([
            'code' => ProgressHelper::PROGRESS_CODE_FINISH,
            'msg' => $msg,
            'step' => self::STEP_SAVE_DATA,
            'finish' => $finishCount,
            'total_count' => $totalCount
        ]);

        return true;
    }

    protected function saveDataRow($taskModel, $dataRow, $dataIndex)
    {
        $res = new \stdClass();
        $res->add = 0;
        $res->failed = 0;

        if (empty($dataRow[self::COL_NAME])
            || empty($dataRow[self::COL_ACTION])
        )
        {
            $res->failed++;
            $this->addResLog("第 {$dataIndex} 行数据不标准或不齐全");
        }

        try
        {
            $model = new TaskCaseModel();
            $model->load([
                'user_id' => $taskModel->user_id,
                'task_id' => $taskModel->id,
                'name' => $dataRow[self::COL_NAME],
                'content' => $dataRow[self::COL_ACTION]
            ]);

            if ($model->save())
            {
                $res->add++;
            }
            else
            {
                $res->failed++;
                $error = json_encode($model->firstErrors, JSON_UNESCAPED_UNICODE);
                $errorRow = json_encode($dataRow, JSON_UNESCAPED_UNICODE);
                LogMgr::addTaskImportLog(__METHOD__, LogMgr::LEVEL_ERROR,
                    "存储数据失败, 行:{$dataIndex}, 异常信息:{$error}, 数据:{$errorRow}");

                $this->addResLog("第 {$dataIndex} 行存储数据失败, 信息:{$error}");
            }
        } catch (\Exception $ex)
        {
            $res->failed++;

            $this->addResLog("第 {$dataIndex} 行存储数据异常");

            $error = json_encode($ex, JSON_UNESCAPED_UNICODE);
            $errorRow = json_encode($dataRow, JSON_UNESCAPED_UNICODE);
            LogMgr::addTaskImportLog(__METHOD__, LogMgr::LEVEL_ERROR,
                "存储数据异常, 行:{$dataIndex}, 数据:{$errorRow}, 异常信息:{$error}");
        }

        return $res;
    }
}