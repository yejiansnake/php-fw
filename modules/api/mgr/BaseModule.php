<?php

namespace app\modules\api\mgr;

use Yii;
use app\lib\common\BaseApiModule;

class BaseModule extends BaseApiModule
{
    function behaviors()
	{
        return [
            [
                'class' => 'app\modules\api\mgr\AccessFilter',
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
