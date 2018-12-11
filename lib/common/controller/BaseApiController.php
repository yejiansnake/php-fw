<?php

namespace app\lib\common\controller;

use app\lib\bl\localization;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

abstract class BaseApiController extends \yii\rest\Controller
{
    //调用日志
    protected static $sysLogCallModelName = null;
    protected static $sysLogCallConfig = [
        'method' => ['GET'],    //排除记录的提交方法
        'action' => [],         //排除记录的接口
    ];

    //错误日志
    protected static $sysLogErrorModelName = null;
    protected static $sysLogErrorConfig = [
        'method' => [],             //排除记录的提交方法
        'action' => [],             //排除记录的接口
        'status_code' => [401],     //排除记录的提交HTTP状态码
        'trace' => [500],           //记录详细 trace 的 status_code
    ];

    //异常描述信息替换为自定义信息
    protected static $exceptionLocalMsg = false;

    //跨域控制开关
    protected static $accessControlAllowEnabled = false;

	public function behaviors()
	{
	    if (static::$accessControlAllowEnabled)
        {
            header('Access-Control-Max-Age: 60');
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Cache-Control, Pragma, Authorization, Origin, X-Requested-With, X-CustomToken, Content-Type, Accept');
        }

        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        Yii::$app->response->format = Response::FORMAT_JSON;

        return $behaviors;
	}

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items'
    ];

	public function actionOptions() //OPTIONS
	{
		$options = ['GET', 'HEAD', 'PUT', 'POST', 'DELETE', 'OPTIONS'];
		Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $options));
		return null;
	}

    public function afterAction($action, $result)
    {
        $this->addSysLogCall($action);

        return parent::afterAction($action, $result);
    }

    protected function addSysLogCall($action)
    {
        if (empty(static::$sysLogCallModelName))
        {
            return;
        }

        if (!empty(static::$sysLogCallConfig)
            && is_array(static::$sysLogCallConfig))
        {
            if (!empty(static::$sysLogCallConfig['method'])
                && in_array($_SERVER['REQUEST_METHOD'], static::$sysLogCallConfig['method']))
            {
                return;
            }

            if (!empty(static::$sysLogCallConfig['action'])
                && in_array($action->id, static::$sysLogCallConfig['action']))
            {
                return;
            }
        }

        $LogModel = static::$sysLogCallModelName;
        $LogModel::addLog();
    }

    //覆盖原始方法（controller）
    public function runAction($id, $params = [])
    {
        $result = null;

        try
        {
            $result = parent::runAction($id, $params);
        }
        catch (\Exception $ex)
        {
            $this->throwAction($id, $ex);

            if (!empty(static::$exceptionLocalMsg)
                && ($ex instanceof HttpException))
            {
                $msg = localization::getValue($ex->getMessage());
                throw new HttpException($ex->statusCode, $msg, $ex->getCode(), $ex);
            }

            throw $ex;
        }

        return $result;
    }

    //抛出异常后执行的的回调,用来抓取和记录日志
    protected function throwAction($id, $ex)
    {
        if (empty(static::$sysLogErrorModelName))
        {
            return;
        }

        $data = [];

        if (!empty(static::$sysLogErrorConfig)
            && is_array(static::$sysLogErrorConfig))
        {
            if ($ex instanceof HttpException
                && !empty(static::$sysLogErrorConfig['status_code']))
            {
                if (in_array($ex->statusCode, static::$sysLogErrorConfig['status_code']))
                {
                    return;
                }
            }

            if (!empty(static::$sysLogErrorConfig['method'])
                && in_array($_SERVER['REQUEST_METHOD'], static::$sysLogErrorConfig['method']))
            {
                return;
            }

            if (!empty(static::$sysLogErrorConfig['action'])
                && in_array($id, static::$sysLogErrorConfig['action']))
            {
                return;
            }

            if ($ex instanceof HttpException
                && !empty(static::$sysLogErrorConfig['trace']))
            {
                if (in_array($ex->statusCode, static::$sysLogErrorConfig['trace']))
                {
                    $data['trace'] = $ex->getTraceAsString();
                }
            }
        }

        $data = $data + [
            'message' => $ex->getMessage(),
            'code' => $ex->getCode(),
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
        ];

        if ($ex instanceof HttpException)
        {
            $data = $data + [
                'name' => $ex->getName(),
                'level' => 1,
                'status_code' => $ex->statusCode,
            ];
        }

        $LogModel = static::$sysLogErrorModelName;
        $LogModel::addLog($data);
    }
}