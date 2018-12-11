<?php

namespace app\models\common;

use Yii;

abstract class SysLogCallModel extends SysLogBaseModel
{
    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['exec_in'], 'integer'];
        return $rules;
    }

    public function load($data, $formName = null)
    {
        $data['exec_in'] = (int)((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000);
        return parent::load($data, $formName);
    }
}