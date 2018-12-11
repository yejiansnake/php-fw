<?php

namespace app\models\common;

use Yii;

abstract class SysLogErrorModel extends SysLogBaseModel
{
    protected static $whereFields = ['id', 'method', 'router', 'session_user_id', 'session_user_name',
        'extra_key_1', 'extra_key_2', 'extra_key_3', 'client_ip', 'host', 'host_ip', 'level', 'status_code'];

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['level', 'status_code', 'code', 'line'], 'integer'];
        $rules[] = [['name', 'file'], 'string', 'max' => 100];
        $rules[] = [['message', 'trace'], 'string'];
        return $rules;
    }
}