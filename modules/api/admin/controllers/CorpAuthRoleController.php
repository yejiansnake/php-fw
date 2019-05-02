<?php

namespace app\modules\api\admin\controllers;

use app\lib\bl\Permission;

class CorpAuthRoleController extends BaseController
{
    protected static $apiModelName = 'app\models\admin\CorpAuthRoleModel';
    protected static $apiModelOptions = ['index', 'view', 'create', 'update', 'delete', 'simple'];

    public function actionPermission()
    {
        return [
            'permission' => Permission::$ADMIN_DATA,
        ];
    }
}