<?php

namespace app\lib\bl;

use Yii;
use app\lib\common\file\CosFile;

class FileMgr
{
    public static function getImageFileMgr()
    {
        return new CosFile('image');
    }
}