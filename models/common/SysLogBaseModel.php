<?php

namespace app\models\common;

use app\lib\bl\LogMgr;
use app\lib\bl\session\CurUserSession;
use app\lib\common\DateTimeEx;
use Yii;

abstract class SysLogBaseModel extends BaseModel
{
    protected static $whereFields = ['id', 'method', 'router', 'session_user_id', 'session_user_name',
        'extra_key_1', 'extra_key_2', 'extra_key_3', 'client_ip', 'host', 'host_ip'];

    protected static $whereRangeFields = [
        'created_at_begin' => ['created_at', '>='],
        'created_at_end' => ['created_at', '<='],
    ];

    protected static $jsonFields = ['params_get', 'params_post', 'extra'];

    //需要重载
    public function rules()
    {
        return [
            [['created_at', 'call_at'], 'safe'],
            [['method', 'router'], 'required'],
            [['id', 'session_user_id', 'client_port', 'host_port'], 'integer'],
            [['method', 'session_id', 'session_user_name'], 'string', 'max' => 50],
            [['router', 'host'], 'string', 'max' => 100],
            [['extra_key_1', 'extra_key_2', 'extra_key_3'], 'string', 'max' => 128],
            [['client_agent'], 'string', 'max' => 500],
            [['client_ip', 'host_ip'], 'string', 'max' => 32],
            [['params_get', 'params_post', 'extra'], 'string'],
        ];
    }

    public function load($data, $formName = null)
    {
        $data['call_at'] = DateTimeEx::getString([
            'time' => empty($_SERVER['REQUEST_TIME']) ? time() : $_SERVER['REQUEST_TIME'],
        ]);

        $data['method'] = $_SERVER['REQUEST_METHOD'];
        $data['router'] = Yii::$app->requestedRoute;

        $queryParams = Yii::$app->request->queryParams;
        if (!empty($queryParams))
        {
            if (isset($queryParams['r']))
            {
                unset($queryParams['r']);
            }

            if (!empty($queryParams))
            {
                $data['params_get'] = $queryParams;
            }
        }

        $bodyParams = Yii::$app->request->bodyParams;
        if (!empty($bodyParams))
        {
            if (isset($bodyParams['pwd']))
            {
                unset($bodyParams['pwd']);
            }

            if (isset($bodyParams['pwd2']))
            {
                unset($bodyParams['pwd2']);
            }

            if (!empty($bodyParams))
            {
                $data['params_post'] = $bodyParams;
            }
        }

        $data['session_id'] = session_id();

        $session = CurUserSession::get();
        if (!empty($session))
        {
            $data['session_user_id'] = empty($session['id']) ? 0 : $session['id'];
            $data['session_user_name'] = empty($session['name']) ? '' : $session['name'];
        }

        $data['client_agent'] = empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'];
        $data['client_ip'] = empty($_SERVER['REMOTE_ADDR']) ? '' : $_SERVER['REMOTE_ADDR'];
        $data['client_port'] = empty($_SERVER['REMOTE_PORT']) ? 0 : $_SERVER['REMOTE_PORT'];

        $data['host'] = empty($_SERVER['HTTP_HOST']) ? '' : $_SERVER['HTTP_HOST'];
        $data['host_ip'] = empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'];
        $data['host_port'] = empty($_SERVER['SERVER_PORT']) ? 0 : $_SERVER['SERVER_PORT'];

        return parent::load($data, $formName);
    }

    public static function addLog(array $params = [])
    {
        $curClass = get_called_class();

        try
        {
            $model = new $curClass();
            $model->load($params);

            if (!$model->save())
            {
                LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR,
                    "class:{$curClass} save model failed,, msg:{$model->getErrorString()}");
            }
        }
        catch (\Exception $ex)
        {
            LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR,
                "class:{$curClass} add log exception, msg:{$ex->getMessage()}");
        }
    }
}