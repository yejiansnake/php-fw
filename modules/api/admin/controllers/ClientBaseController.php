<?php

namespace app\modules\api\admin\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use app\models\admin\CompanyModel;

abstract class ClientBaseController extends BaseController
{
    protected $curCompany = null;

    protected function initCompany()
    {
        if (!empty(static::$apiModelName))
        {
            $companyID = Yii::$app->request->getQueryParam('company_id');

            if (empty($companyID))
            {
                $companyID = Yii::$app->request->getBodyParam('company_id');

                if (empty($companyID))
                {
                    throw new BadRequestHttpException('company_id param invalid');
                }
            }

            $model = CompanyModel::getOne(['id' => $companyID]);

            if (empty($model))
            {
                throw new BadRequestHttpException('company not exist');
            }

            $this->curCompany = $model->toArray();
            $apiModelName = static::$apiModelName;
            $apiModelName::setDbByKeyName($model->key_name);
        }
    }

    public function runAction($id, $params = [])
    {
        $this->initCompany();

        return parent::runAction($id, $params);
    }
}