<?php
/**
 * EXCEL 文件数据导入
 * User: yejian
 * Date: 2016/5/17
 * Time: 14:55
 */

namespace app\lib\common;

require_once (APP_SYS_PATH . '/lib/vendor/Excel/PHPExcel.php');

class ExcelImport
{
    private $excel = null;
    private $activeSheet = null;

//    const ROW_INDEX_START = 1;
//    const COL_INDEX_START = 0;

    public function __construct()
    {

    }

    public function load($filePath)
    {
        $this->excel = \PHPExcel_IOFactory::load($filePath);
        $this->activeSheet = $this->excel->getSheet(0);
    }

    public function getRowCount()
    {
        return $this->activeSheet->getHighestRow();
    }

    public function getColCount()
    {
        //columnIndexFromString('A') == 1，所以这里需要注意索引的起始位置为1，并非下列 getCellByColumnAndRow 的 0
        $highestColumn = $this->activeSheet->getHighestColumn();
        return \PHPExcel_Cell::columnIndexFromString($highestColumn);
    }

    //这里需要统一 col 和 row 的起始位置才行，否则使用起来太容易混乱了，一下0一下1
    public function getValue($row, $col)
    {
        $row += 1;
        //1, 8 对应 cell B8 : getCellByColumnAndRow($pColumn = 0, $pRow = 1)
        $value = $this->activeSheet->getCellByColumnAndRow($col, $row)->getValue();

        if ($value == null)
        {
            return '';
        }

        if($value instanceof \PHPExcel_RichText)
        {
            $value = $value->__toString();
        }

        $value = trim($value);

        return $value;
    }

    public function getRow($row)
    {
        $rowData = [];
        $colCount = $this->getColCount();

        for ($col = 0; $col < $colCount; $col++)
        {
            $rowData[] = $this->getValue($row, $col);
        }

        return $rowData;
    }

    public function getTable(
        $startRowIndex = 0,
        $startColIndex = 0,
        $rowCount = null,
        $colCount = null)
    {
        if (!isset($rowCount))
        {
            $rowCount = $this->getRowCount();
        }

        if (!isset($colCount))
        {
            $colCount = $this->getColCount();
        }

        $dataSet = [];
        for ($row = $startRowIndex; $row <= $rowCount; $row++)     //行数从第一行开始
        {
            $dataSet[$row] = [];

            for ($col = $startColIndex; $col < $colCount; $col++)    //列数从第0列开始
            {
                $dataSet[$row][] = $this->getValue($row, $col);
            }
        }

        return $dataSet;
    }

    //参数内容：
    //      ignoreOne : 忽略第一行（参数存在即可生效）
    //      colCount  : 获取的列数
    public function getAll(array $params = [])
    {
        $startRow = 0;
        $rowCount = $this->getRowCount();
        $colCount = $this->getColCount();
        if (isset($params['ignoreOne']))
        {
            $startRow++;
            $rowCount--;
        }

        if (isset($params['colCount']))
        {
            $colCount = $params['colCount'];
        }

        return $this->getTable($startRow, 0, $rowCount, $colCount);
    }

    public static function readExcel($filePath)
    {
        $excel = new ExcelImport();
        $excel->load($filePath);
        return $excel->getAll();
    }

    public static function toLocalDate($value, $default = null)
    {
        if ($value == null || $value == '')
        {
            return $default;
        }

        //处理时间
        $time = \PHPExcel_Shared_Date::ExcelToPHP($value);
        return DateTimeEx::getString(['time' => $time, 'format' => DateTimeEx::DATE_FORMAT]);
    }
}