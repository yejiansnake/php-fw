<?php

namespace app\modules\api\admin\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use app\lib\common\Guid;
use app\models\client\SysPropsModel;

class ClientPropsController extends ClientBaseController
{
    protected static $apiModelName = 'app\models\client\SysPropsModel';
    protected static $isPagination = false;


    public function actionSet()
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['type']) || empty($params['id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        if (empty($params['name']))
        {
            $params['name'] = '0';
        }

        return SysPropsModel::setProp($params);
    }

    public function actionGet()
    {
        $params = Yii::$app->request->queryParams;

        if (empty($params['type']) || empty($params['id']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        return SysPropsModel::getProp($params['type'], $params['id']);
    }

    protected function actionCreateImp(array $params = [])
    {
        if (empty($params['type']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        if (empty($params['id']))
        {
            $params['id'] = self::getNextID($params['type']);
        }

        if (empty($params['name']))
        {
            $params['name'] = Guid::toString();
        }

        return parent::actionCreateImp($params);
    }

    private function getNextID($type, $startID = 1)
    {
        $apiModelName = static::$apiModelName;
        $maxID = $apiModelName::getMax('id', ['type' => $type, '@delete' => 1]);

        if (empty($maxID))
        {
            $maxID = $startID;
        }
        else
        {
            $maxID += 1;
        }

        return $maxID;
    }
}