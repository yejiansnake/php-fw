<?php

namespace app\lib\common\controller;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\lib\common\PaginationHelper;
use app\lib\common\session\BaseCurUserSession;

abstract class ApiBaseController extends BaseApiController
{
	//可以重载的apiModel
	protected static $apiModelName = null;  //数据模块名称（包含命名空间，例如: app\models\mgr\LogErrorModel）

    const ACTION_SIMPLE = 'simple';
    const ACTION_INDEX = 'index';
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    //API支持的基础接口
    //类型 simple 返回所有只包含 id 与 name 的数据项数组
    protected static $apiModelOptions = [
        self::ACTION_INDEX,
        self::ACTION_VIEW,
        self::ACTION_CREATE,
        self::ACTION_UPDATE,
        self::ACTION_DELETE,
    ];

    protected static $apiModelParams = [];  //API基础接口默认添加的参数
    protected static $isPagination = true;  //index 接口是否支持分页

	//可以重载的特殊处理
	//protected static $publicPicFields = [];

	//跨域控制开关
	protected static $accessControlAllowEnabled = false;

    protected static $exceptionLocalMsg = true;

	protected $curUser = null;
	protected $curUserID = null;

	protected function afterInitCurUser(&$curUser)
    {

    }

    final protected function resetCurUser($userInfo)
    {
        BaseCurUserSession::set($userInfo);
        self::initCurUser($userInfo);
    }

    final private function initCurUser(&$curUserSession)
    {
        $this->curUser = $curUserSession;
        $this->curUserID = $this->curUser['id'];
        $this->afterInitCurUser($curUserSession);
    }

    protected function getCurUser()
    {
        $session = BaseCurUserSession::get();

        if (empty($session))
        {
            return null;
        }

        return $session;
    }

	public function __construct($id, $module, $config = [])
	{
        $session = BaseCurUserSession::get();

        if (!empty($session))
        {
            self::initCurUser($session);
        }

		parent::__construct($id, $module, $config);
	}

    public function getCurResource($type, $resourceParam = null, $noResourceReturn = -99999)
    {
        $res = [];

        if (empty($this->curUser['resource']))
        {
            return $noResourceReturn;
        }
        else
        {
            if (empty($this->curUser['resource'][$type]))
            {
                return $noResourceReturn;
            }

            $resource = $this->curUser['resource'][$type];

            if (empty($resourceParam))
            {
                return $resource;
            }
            else
            {
                if (is_array($resourceParam))
                {
                    foreach ($resourceParam as $param)
                    {
                        if (in_array($param, $resource))
                        {
                            $res[] = $param;
                        }
                    }

                    if (empty($res))
                    {
                        return $noResourceReturn;
                    }
                }
                else
                {
                    if (!in_array($resourceParam, $resource))
                    {
                        return $noResourceReturn;
                    }
                    else
                    {
                        $res = $resourceParam;
                    }
                }
            }
        }

        return $res;
    }

	//-----------------------------------------------------------------------------------------------
	//默认基础 REST API 支持

    public function actionSimple()
    {
        $params = Yii::$app->request->queryParams;
        return $this->actionSimpleImp($params);
    }

    protected function actionSimpleImp(array $params = [], array $fieldNames = ['id', 'name'])
    {
        if (empty(static::$apiModelName) || !in_array(self::ACTION_SIMPLE, static::$apiModelOptions))
        {
            throw new NotFoundHttpException();
        }

        if (!empty(static::$apiModelParams))
        {
            $params = array_merge($params, static::$apiModelParams);
        }

        $apiModelName = static::$apiModelName;

        $models = $apiModelName::get($params);

        $res = [];
        foreach ($models as $model)
        {
            if (!empty($fieldNames))
            {
                $item = [];

                foreach ($fieldNames as $fieldName)
                {
                    $item[$fieldName] = $model->{$fieldName};
                }

                $res[] = $item;
            }
        }

        return ['items' => $res];
    }

	public function actionIndex()
	{
		$params = Yii::$app->request->queryParams;
		return $this->actionIndexImp($params);
	}

	protected function actionIndexImp(array $params = [], array $orderByParams = ['id' => SORT_DESC])
	{
		if (empty(static::$apiModelName) || !in_array(self::ACTION_INDEX, static::$apiModelOptions))
		{
			throw new NotFoundHttpException();
		}

        if (!empty(static::$apiModelParams))
        {
            $params = array_merge($params, static::$apiModelParams);
        }

		$apiModelName = static::$apiModelName;

        $pagination = false;
        if (static::$isPagination)
        {
            $pagination = PaginationHelper::getPagination($params);
        }

		$query = $apiModelName::getQuery($params);

		if (empty($params['@orderBy']) && !empty($orderByParams))
		{
			$query->orderBy($orderByParams);
		}

		$adp = new ActiveDataProvider([
			'query' => $query,
			'pagination' => $pagination
		]);

		return $adp;
	}

	public function actionView($id)
	{
		return $this->actionViewImp(['id' => $id]);
	}

	protected function actionViewImp(array $params = [])
	{
		if (empty(static::$apiModelName) || !in_array(self::ACTION_VIEW, static::$apiModelOptions))
		{
			throw new NotFoundHttpException();
		}

        if (!empty(static::$apiModelParams))
        {
            $params = array_merge($params, static::$apiModelParams);
        }

		$apiModelName = static::$apiModelName;

		$model = $apiModelName::getOne($params);

		if (empty($model))
		{
			throw new NotFoundHttpException("entity not found");
		}

		return $model;
	}

	public function actionCreate()
	{
		$params = Yii::$app->request->bodyParams;

//        foreach (static::$publicPicFields as $fieldName)
//        {
//            if (!empty($params[$fieldName]))
//            {
//                $filePath = ImageMgr::saveImageBase64(0, $params[$fieldName]);
//
//                if (empty($filePath))
//                {
//                    throw new ServerErrorHttpException("pic save failed");
//                }
//
//                $params[$fieldName] = $filePath;
//            }
//        }

		return $this->actionCreateImp($params);
	}

	protected function actionCreateImp(array $params = [])
	{
		if (empty(static::$apiModelName) || !in_array(self::ACTION_CREATE, static::$apiModelOptions))
		{
			throw new NotFoundHttpException();
		}

        if (!empty(static::$apiModelParams))
        {
            $params = array_merge($params, static::$apiModelParams);
        }

		$apiModelName = static::$apiModelName;

		$model = new $apiModelName();
		$model->load($params, '');

		if (!$model->save())
		{
			if (YII_ENV_DEV)
			{
				$error = json_encode($model->firstErrors, JSON_UNESCAPED_UNICODE);
				throw new ServerErrorHttpException($error);
			}

			throw new ServerErrorHttpException('entity save failed');
		}

		return $model;
	}

	public function actionUpdate($id)
	{
		$updateParams = Yii::$app->request->bodyParams;

		return $this->actionUpdateImp(['id' => $id], $updateParams);
	}

	protected function actionUpdateImp(array $params = [], array $updateParams = [])
	{
		if (empty(static::$apiModelName) || !in_array(self::ACTION_UPDATE, static::$apiModelOptions))
		{
			throw new NotFoundHttpException();
		}

        if (!empty(static::$apiModelParams))
        {
            $params = array_merge($params, static::$apiModelParams);
            $updateParams = array_merge($updateParams, static::$apiModelParams);
        }

		$apiModelName = static::$apiModelName;

		$model = $apiModelName::getOne($params);

		if (empty($model))
		{
			throw new NotFoundHttpException("entity not found");
		}

//        foreach (static::$publicPicFields as $fieldName)
//        {
//            if (!empty($updateParams[$fieldName]))
//            {
//                if ($model[$fieldName] != $updateParams[$fieldName])
//                {
//                    $filePath = ImageMgr::saveImageBase64(0, $updateParams[$fieldName]);
//
//                    if (empty($filePath))
//                    {
//                        throw new ServerErrorHttpException("pic save failed");
//                    }
//
//                    $updateParams[$fieldName] = $filePath;
//                }
//            }
//        }

		$model->load($updateParams, '');

		if (!$model->save())
		{
			if (YII_ENV_DEV)
			{
				$error = json_encode($model->firstErrors, JSON_UNESCAPED_UNICODE);
				throw new ServerErrorHttpException($error);
			}

			throw new ServerErrorHttpException('entity save failed');
		}

		return $model;
	}

	public function actionDelete($id)
	{
		return $this->actionDeleteImp(['id' => $id]);
	}

	protected function actionDeleteImp(array $params = [])
	{
		if (empty(static::$apiModelName) || !in_array(self::ACTION_DELETE, static::$apiModelOptions))
		{
			throw new NotFoundHttpException();
		}

        if (!empty(static::$apiModelParams))
        {
            $params = array_merge($params, static::$apiModelParams);
        }

		$apiModelName = static::$apiModelName;

		$model = $apiModelName::getOne($params);

		if (empty($model))
		{
			throw new NotFoundHttpException("entity not found");
		}

		if (!$model->delete())
		{
			throw new ServerErrorHttpException('entity delete failed');
		}

		return $model;
	}
}