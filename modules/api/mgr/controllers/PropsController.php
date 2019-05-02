<?php

namespace app\modules\api\mgr\controllers;

use app\lib\bl\Enum;
use Yii;
use app\models\client\SysPropsModel;
use yii\web\BadRequestHttpException;

class PropsController extends BaseController
{
    protected static $apiModelName = 'app\models\client\SysPropsModel';
    protected static $apiModelOptions = [
        self::ACTION_INDEX,
        self::ACTION_VIEW,
        self::ACTION_SIMPLE,
    ];

    protected static $isPagination = false;

    public function actionSimple()
    {
        $params = Yii::$app->request->queryParams;

        if (empty($params['type']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $props = $this->actionSimpleImp($params, ['tid', 'name']);

        $items = [];
        foreach ($props['items'] as $prop)
        {
            $items[] = ['id' => $prop['tid'], 'name' => $prop['name']];
        }

        return ['items' => $items];
    }

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

    protected function getPropOne($type, $tid)
    {
        $model = SysPropsModel::getOne(['type' => $type, 'tid' => $tid]);

        if (empty($model))
        {
            return [];
        }

        return $model->toArray();
    }

    public function actionGetPropOne()
    {
        $params = Yii::$app->request->get();
        if (empty($params) || empty($params['type']) || empty($params['tid']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $model = SysPropsModel::getOne(['type' => $params['type'], 'tid' => $params['tid']]);

        if (empty($model))
        {
            return [];
        }

        return $model->toArray();
    }
}