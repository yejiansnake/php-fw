<?php

namespace app\modules\api\admin\controllers;

use app\lib\bl\Permission;

class ClientCorpAuthRoleController extends ClientBaseController
{
    protected static $apiModelName = 'app\models\client\CorpAuthRoleModel';
    protected static $apiModelOptions = ['index', 'view', 'create', 'update', 'delete', 'simple'];

    /**
     * @api {get} /api/admin/client-corp-auth-role/permission 权限列表
     * @apiGroup Admin-Auth-Role
     * @apiName Permission
     *
     * @apiParam {Number} company_id 公司编号
     *
     * @apiSuccess {Object[]}  permission
     * @apiSuccess {String}   permission.id
     * @apiSuccess {String}   permission.name
     */
    public function actionPermission()
    {
        return [
            'permission' => Permission::$MGR_DATA,
        ];
    }
}