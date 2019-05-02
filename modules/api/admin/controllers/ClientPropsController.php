<?php

namespace app\modules\api\admin\controllers;

use app\lib\bl\FileMgr;
use Yii;
use yii\web\BadRequestHttpException;
use app\lib\common\Guid;
use app\lib\bl\Enum;
use app\models\client\SysPropsModel;
use yii\web\ServerErrorHttpException;

class ClientPropsController extends ClientBaseController
{
    /**
     * @api {post} /api/admin/client-props 创建记录并自增tid
     * @apiGroup Admin-Client-Props
     * @apiName Create
     *
     * @apiParam {Integer} company_id (必传)公司编号
     * @apiParam {Integer} type (必传)预定义好的类型编号
     * @apiParam {String} name 名称
     * @apiParam {Integer} pid 父级编号，默认0
     * @apiParam {String} desc 描述
     * @apiParam {Integer} value1 具体值
     * @apiParam {Integer} value2 具体值
     * @apiParam {Integer} value3 具体值
     * @apiParam {Integer} value4 具体值
     * @apiParam {Integer} value5 具体值
     * @apiParam {String} value6 具体值
     * @apiParam {String} value7 具体值 Json 格式
     * @apiParam {DateTime} value8 具体值
     *
     * @apiSuccess {Object} model
     */

    /**
     * @api {post} /api/admin/client-props/set 创建记录
     * @apiGroup Admin-Client-Props
     * @apiName Set
     *
     * @apiParam {Integer} company_id (必传)公司编号
     * @apiParam {Integer} type (必传)预定义好的类型编号
     * @apiParam {Integer} tid (必传)类型下面具体含义的编号
     * @apiParam {String} name 名称
     * @apiParam {Integer} pid 父级编号，默认0
     * @apiParam {String} desc 描述
     * @apiParam {Integer} value1 具体值
     * @apiParam {Integer} value2 具体值
     * @apiParam {Integer} value3 具体值
     * @apiParam {Integer} value4 具体值
     * @apiParam {Integer} value5 具体值
     * @apiParam {String} value6 具体值
     * @apiParam {String} value7 具体值 Json 格式
     * @apiParam {DateTime} value8 具体值
     *
     * @apiSuccess {Object} model
     */

    /**
     * @api {get} /api/admin/client-props/get-type 获取某一(type)下的所有数据
     * @apiGroup Admin-Client-Props
     * @apiName GetType
     *
     * @apiParam {Integer} company_id (必传)公司编号
     * @apiParam {Integer} type (必传)预定义好的类型编号(需要开通白名单)
     *
     * @apiSuccess {Object[]} items
     */


    protected static $apiModelName = 'app\models\client\SysPropsModel';
    protected static $apiModelOptions = [
        self::ACTION_INDEX,
        self::ACTION_VIEW,
        self::ACTION_CREATE,
        self::ACTION_UPDATE,
        self::ACTION_DELETE,
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

    public function actionSet()
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['type']) || empty($params['tid']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        if (empty($params['name']))
        {
            $params['name'] = '0';
        }

        return SysPropsModel::setProp($params);
    }

    public function actionGetType()
    {
        $params = Yii::$app->request->queryParams;
        if (empty($params['type'])
            || !in_array($params['type'], [
                    // 白名单，有需求增加
                    Enum::CLIENT_PM_TYPE_DIMENSION,
                    Enum::CLIENT_PM_TYPE_MENU
                ]
            )
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        $propsModels = SysPropsModel::getProps($params['type']);
        return empty($propsModels) ? ['items' => []] : ['items' => $propsModels];
    }

    public function actionGet()
    {
        $params = Yii::$app->request->queryParams;

        if (empty($params['type']) || empty($params['tid']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        return SysPropsModel::getProp($params['type'], $params['tid']);
    }

    protected function actionCreateImp(array $params = [])
    {
        if (empty($params['type']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        if (empty($params['tid']))
        {
            $params['tid'] = self::getNextID($params['type']);
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
        $maxID = $apiModelName::getMax('tid', ['type' => $type, '@delete' => 1]);

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

    /**
     * @api {post} /api/admin/client-props/upload-file 上传文件
     * @apiGroup Admin-Client-Props
     * @apiName UploadFile
     *
     * @apiParam {Integer} company_id (必传)公司编号
     * @apiParam {String} file_type (必传)文件类型(目前只支持 image 值)
     * @apiParam {File} file （必传）文件
     * @apiParam {String} key 文件名
     *
     * @apiSuccess {Object} data key代表文件名，path代表路径
     */

    public function actionUploadFile()
    {
        $params = Yii::$app->request->bodyParams;

        if (empty($params['file_type']) || !isset($_FILES['file']))
        {
            throw new BadRequestHttpException('params invalid');
        }

        $dir = '/tmp/talent';
        if (!file_exists($dir))
        {
            if (!mkdir($dir, 0777, true))
            {
                throw new ServerErrorHttpException('创建文件夹失败，请联系管理员');
            }
        }

        $result = [];
        $file = $dir . '/' . time() . rand(10000, 99999);
        move_uploaded_file($_FILES['file']['tmp_name'], $file);
        if ($params['file_type'] == 'image')
        {
            $fileObj = FileMgr::getImageFileMgr();
            $result = $fileObj->save([
                'data' => file_get_contents($file),
                'key' => empty($params['key']) ? null : $params['key']
            ]);
            if (empty($result['key']) || empty($result['path']))
            {
                throw new ServerErrorHttpException('上传失败');
            }
        }

        unlink($file);
        return ['data' => $result];
    }
}