<?php
/**
 * api 基础类
 */

namespace app\modules\api\admin\controllers;

use Yii;
use app\lib\common\controller\ApiBaseController;

abstract class BaseController extends ApiBaseController
{
    protected static $sysLogCallModelName = 'app\models\admin\SysLogAdminCallModel';
    protected static $sysLogErrorModelName = 'app\models\admin\SysLogAdminErrorModel';
}