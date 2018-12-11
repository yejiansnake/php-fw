<?php

namespace app\modules\api\app\controllers;

use app\lib\bl\Enum;
use Yii;
use app\models\client\SysPropsModel;
use yii\web\BadRequestHttpException;

class PropsController extends BaseController
{
    /**
     * @param $type
     * @return array
     * @throws BadRequestHttpException
     */
    protected function getProps($type)
    {
        $models = SysPropsModel::get(['type' => $type]);

        if (empty($models))
        {
            return [];
        }

        $res = [];
        foreach ($models as $model)
        {
            $res[] = $model->toArray();
        }

        return $res;
    }

    protected function getPropOne($type, $id)
    {
        $model = SysPropsModel::getOne(['type' => $type, 'id' => $id]);

        if (empty($model))
        {
            return [];
        }

        return $model->toArray();
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
//    public function actionGetProps()
//    {
//        return ['items' => SysPropsModel::get(['type' => [
//            Enum::CLIENT_PM_TYPE_FEED_INDEX,
//        ]])];
//    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionGetPropOne()
    {
        $params = Yii::$app->request->get();
        if (empty($params) || empty($params['type']) || empty($params['id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $model = SysPropsModel::getOne(['type' => $params['type'], 'id' => $params['id']]);

        if (empty($model))
        {
            return [];
        }

        return $model->toArray();
    }
}