<?php

namespace app\modules\api\admin;

use Yii;
use app\lib\common\BaseApiModule;

class BaseModule extends BaseApiModule
{
    function behaviors()
	{
        return [
            [
                'class' => 'app\modules\api\admin\AccessFilter',
                'except' => [
                    'home/index',
                    'home/test',
                    'auth/pre',
                    'auth/login',
                ]
            ]
        ];
    }
}
