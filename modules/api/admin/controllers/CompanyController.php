<?php

namespace app\modules\api\admin\controllers;


use Yii;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;
use app\lib\common\Guid;
use app\lib\bl\CachedMgr;
use app\lib\bl\CompanyUrlHelper;
use app\lib\common\HttpHelper;
use app\models\admin\CompanyModel;

class CompanyController extends BaseController
{
    protected static $apiModelName = 'app\models\admin\CompanyModel';
    protected static $apiModelOptions = ['index', 'view', 'create', 'update', 'simple'];    //API支持的基础接口

    protected function actionCreateImp(array $params = [])
    {
        if (empty($params['en_short_name']) && empty($params['key_name']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $params['token'] = Guid::ToStringWeb();
        $params['key_name'] = empty($params['key_name']) ? strtolower($params['en_short_name']) : strtolower($params['key_name']);

        return parent::actionCreateImp($params);
    }

    public function actionCreate()
    {
        $model = $this->actionCreateImp(Yii::$app->request->bodyParams);

        if (!CompanyModel::createCompanyDb($model->key_name))
        {
            throw new ServerErrorHttpException('创建公司数据库失败');
        }

        return $model;
    }

    protected function actionUpdateImp(array $params = [], array $updateParams = [])
    {
        if (!empty($updateParams['key_name']))
        {
            unset($updateParams['key_name']);
        }

        if (!empty($updateParams['token']))
        {
            unset($updateParams['token']);
        }

        return parent::actionUpdateImp($params, $updateParams);
    }

    public function actionLoginMgr()
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['company_id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $model = CompanyModel::getOne(['id' => $params['company_id'], 'is_enable' => 1]);

        if (empty($model))
        {
            throw new BadRequestHttpException('company not invalid');
        }

        $token = Guid::ToStringWeb($model->key_name);
        if (!CachedMgr::set(CachedMgr::getCompanyMgrKey($token), [
            'id' => -1 * $this->curUserID,
            'name' => $this->curUser['name'],
        ], 30))
        {
            throw new BadRequestHttpException('login token create failed');
        }

        $mgrUrl = CompanyUrlHelper::getMgrUrl($model->id);

        return [
            'url' => HttpHelper::wrapUrl("{$mgrUrl}/mgr-sso/login", ['token' => $token]),
        ];
    }
}