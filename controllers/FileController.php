<?php

namespace app\controllers;

use Yii;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\lib\common\FileHelper;

//只处理存储在数据库上的数据
class FileController extends BaseController
{
    private function output($name, $key)
    {
        if (empty($name) || empty($key))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $fileMgr = new FileHelper($name);
        $res = $fileMgr->get(['key' => $key, 'cache' => 1]);

        if (empty($key))
        {
            throw new NotFoundHttpException('image not exist');
        }

        Yii::$app->response->format = Response::FORMAT_RAW;
        header("Content-Type: {$res['mine_type']}");
        header("Content-Length: {$res['size']}");
        file_put_contents('php://output', $res['content']);
        die();
    }
}