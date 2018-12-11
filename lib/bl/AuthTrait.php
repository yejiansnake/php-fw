<?php

namespace app\lib\bl;

use app\lib\bl\session\CurUserSession;
use yii\web\UnauthorizedHttpException;

trait AuthTrait
{
    abstract public function actionLogin();

    public function actionLogout()
    {
        CurUserSession::clear();
        return ['message' => 'logout'];
    }

    public function actionCurrent()
    {
        if (!CurUserSession::exist())
        {
            throw new UnauthorizedHttpException('not login', Enum::EXCEPTION_CODE_SILENT);
        }

        return [
            'data' => [
                'user' => CurUserSession::get(),
                'static' => Enum::get(),
            ],
        ];
    }
}