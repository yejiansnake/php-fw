<?php
namespace app\modules\event\controllers;

use Yii;
use app\lib\common\controller\EventBaseController;

abstract class BaseController extends EventBaseController
{
    public function actionReceive()
    {
        return 'success';
    }
}