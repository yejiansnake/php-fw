<?php

namespace app\modules\api\admin\controllers;

use app\lib\bl\Enum;
use app\lib\common\Guid;
use app\models\admin\SysPropsModel;
use Yii;
use yii\web\BadRequestHttpException;

abstract class BasePropController extends BaseController
{
    protected static $apiModelName = 'app\models\admin\SysPropsModel';

    protected static $apiModelParams = ['type' => 0];
    protected static $start_id = 1;

    public function actionSet()
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $params['type'] = static::$apiModelParams['type'];

        if (empty($params['name']))
        {
            $params['name'] = Enum::getValue(Enum::$CLIENT_PM_MAP, $params['type'], $params['id']);
        }

        return SysPropsModel::setProp($params);
    }

    protected function actionCreateImp(array $params = [])
    {
        if (empty($params['id']))
        {
            $params['id'] = self::getNextID();
        }

        if (empty($params['name']))
        {
            $params['name'] = Guid::toString();
        }

        return parent::actionCreateImp($params);
    }

    private function getNextID()
    {
        $maxID = SysPropsModel::getMax('id', ['type' => static::$apiModelParams['type'], '@delete' => 1]);

        if (empty($maxID))
        {
            $maxID = static::$start_id;
        }

        else
        {
            $maxID += 1;
        }

        return $maxID;
    }
}