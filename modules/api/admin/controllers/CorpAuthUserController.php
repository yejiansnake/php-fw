<?php

namespace app\modules\api\admin\controllers;


use app\models\admin\CorpAuthUserModel;
use app\models\client\CorpUserModel;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CorpAuthUserController extends BaseController
{
    protected static $apiModelName = 'app\models\admin\CorpAuthUserModel';
    protected static $apiModelOptions = ['index', 'update'];    //API支持的基础接口


    /**
     * @return array|mixed
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->bodyParams;
        if (empty($params['id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $models = CorpUserModel::get(['id' => $params['id']]);
        if (empty($models))
        {
            throw new NotFoundHttpException('not found user');
        }

        foreach ($models as $model)
        {
            $userInfo = [
                'wx_id' => $model->wx_id,
                'code' => $model->code,
                'name' => $model->name,
                'en_name' => $model->en_name
            ];
            CorpAuthUserModel::saveOne($userInfo);
        }

        return ['msg' => 'ok'];
    }
}