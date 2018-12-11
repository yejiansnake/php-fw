<?php

namespace app\modules\api\app;

use Yii;
use app\lib\common\BaseApiModule;

class BaseModule extends BaseApiModule
{
    function behaviors()
	{
        return [
            [
                'class' => 'app\modules\api\app\AccessFilter',
                'except' => [
                    'home/index',
                    'home/test',
                    'auth/base',
                    'auth/pre',
                    'auth/login',
                ]
            ]
        ];
    }
}
