<?php

namespace app\modules\api\admin\controllers;

class CorpUserController extends BaseController
{
    protected static $apiModelName = 'app\models\client\CorpUserModel';
    protected static $apiModelOptions = ['index'];    //API支持的基础接口
}