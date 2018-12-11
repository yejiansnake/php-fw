<?php

namespace app\modules\api\app;

use Yii;
use app\lib\bl\session\CurUserSession;
use yii\web\ForbiddenHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\Response;
use app\lib\common\BaseAccessFilter;
use app\lib\bl\Enum;

class AccessFilter extends BaseAccessFilter
{
    public $enable = true;

    public function getUserResource()
    {
        $userInfo = CurUserSession::get();
        if (empty($userInfo))
        {
            return null;
        }

        return [
            BaseAccessFilter::ACCESS_TYPE => empty($userInfo['type']) ? [] : $userInfo['type'],
            AccessFilter::ACCESS_ROLE => empty($userInfo['roles']) ? [] : $userInfo['roles'],
            BaseAccessFilter::ACCESS_PERMISSION => empty($userInfo['permissions']) ? [] : $userInfo['permissions'],
        ];
    }

    public function getRules()
    {
        return [
            "controller" => [
            ],
            "action" => [
            ],
        ];
    }

    public function isSuperAdmin($userResource)
    {
        return $userResource[AccessFilter::ACCESS_TYPE] == 0 ? true : false;
    }

    public function onUnauthorized($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        throw new UnauthorizedHttpException("没有权限访问", Enum::EXCEPTION_CODE_SILENT);
    }

    public function onForbidden($action)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        throw new ForbiddenHttpException("没有权限访问");
    }
}