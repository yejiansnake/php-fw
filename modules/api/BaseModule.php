<?php

namespace app\modules\api;

use \yii\base\Module;

class BaseModule extends Module
{
    public function init()
    {
        parent::init();

        $this->modules = [
            'admin' => [
                'class' => 'app\modules\api\admin\BaseModule',
            ],
            'app' => [
                'class' => 'app\modules\api\app\BaseModule',
            ],
            'mgr' => [
                'class' => 'app\modules\api\mgr\BaseModule',
            ],
        ];
    }
}