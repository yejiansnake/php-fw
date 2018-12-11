<?php
/**
 * EXCEL 数据导出
 * User: yejian
 * Date: 2016/5/16
 * Time: 15:38
 */

namespace app\lib\common;

require_once (APP_SYS_PATH . '/lib/vendor/Excel/PHPExcel.php');

class ExcelExport
{
    public $title = '';     //SheetTitle

    private $excel = null;
    private $activeSheet = null;
    private $dataRow = 1;

    public function __construct()
    {
        $this->excel = new \PHPExcel();
        $this->activeSheet = $this->excel->setActiveSheetIndex(0);

        $this->init();
    }

    public function getHead()
    {
        return [];
    }

    private function init()
    {
        if (!empty($this->title))
        {
            $this->activeSheet->setTitle($this->title);
        }

        $this->initHead();

        return false;
    }

    public function initHead()
    {
        $head = $this->getHead();

        if (empty($head))
        {
            return;
        }

        $this->addRow($head);
    }

    public function addRow(array $row = [])
    {
        if (empty($row))
        {
            return;
        }

        $curCol = 0;    //列从0开始算起，行从1开始算起
        foreach ($row as $value)
        {
            $this->activeSheet->getCellByColumnAndRow($curCol, $this->dataRow)
                ->setValueExplicit($value, \PHPExcel_Cell_DataType::TYPE_STRING);

            $curCol++;
        }

        $this->dataRow++;
    }

    public function addRows(array $rows = [])
    {
        if (empty($rows))
        {
            return;
        }

        foreach ($rows as $row)
        {
            $this->addRow($row);
        }
    }

    public function addRowEx(array $row = [])
    {
        if (empty($row))
        {
            return;
        }

        $curCol = 0;    //列从0开始算起，行从1开始算起
        foreach ($row as $data)
        {
            if (!empty($data['color']))   //argb
            {
                $this->activeSheet->getStyleByColumnAndRow($curCol, $this->dataRow)
                    ->getFont()->getColor()->setARGB($data['color']);
            }

            $this->activeSheet->getCellByColumnAndRow($curCol, $this->dataRow)
                ->setValueExplicit($data['value'], \PHPExcel_Cell_DataType::TYPE_STRING2);

            $curCol++;
        }

        $this->dataRow++;
    }

    public function addRowsEx(array $rows = [])
    {
        if (empty($rows))
        {
            return;
        }

        foreach ($rows as $row)
        {
            $this->addRowEx($row);
        }
    }

    public function output($fileName)
    {
        $objWriter = new \PHPExcel_Writer_Excel5($this->excel);
        $objWriter->save($fileName);
    }

    public function outputStream($fileName)
    {
        // Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"{$fileName}.xls\"");
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $this->output('php://output');
    }

    public function customizeAdd(array $data)
    {
        if (!empty($data['value']))
        {
            $this->activeSheet->getCellByColumnAndRow($data['value']['col'], $data['value']['row'])
                ->setValueExplicit($data['value']['value'], \PHPExcel_Cell_DataType::TYPE_STRING);
        }

        if (!empty($data['merge']))
        {
            $this->activeSheet->mergeCellsByColumnAndRow(
                $data['merge']['col1'],
                $data['merge']['row1'],
                $data['merge']['col2'],
                $data['merge']['row2']
            );
        }
    }
}