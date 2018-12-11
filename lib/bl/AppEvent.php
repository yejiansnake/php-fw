<?php

namespace app\lib\bl;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\lib\common\BaseAppEvent;
use app\lib\common\LoadMgr;
use app\lib\bl\session\CompanySession;
use app\models\admin\CompanyModel;

class AppEvent extends BaseAppEvent
{
    const TYPE_KEY_NAME = 0;
    const TYPE_MODULE_AND_KEY_NAME = 1;
    const TYPE_WX_APP_ID = 2;

    private static $config = [
    ];

    //初始化额外的数据库全局对象
    protected static function initCompany()
    {
        if (empty(LoadMgr::$subSysName))
        {
            return;
        }

        if (empty(Yii::$app->params['client'])
            || empty(Yii::$app->params['client']['db']))
        {
            throw new ServerErrorHttpException('client db config not exist');
        }

        $keyName = self::getCompanyKeyName();

        if (!empty($keyName))
        {
            Yii::$app->set('db_client', CompanyModel::getCompanyDbObject($keyName));
        }
    }

    private static function getCompanyKeyName()
    {
        if (array_key_exists(LoadMgr::$sysModule, self::$config))
        {
            switch (self::$config[LoadMgr::$sysModule])
            {
                case self::TYPE_KEY_NAME:
                    {
                        return self::initCompanyForKeyName();
                    }
                    break;
                case self::TYPE_MODULE_AND_KEY_NAME:
                    {
                        return self::initCompanyForKeyName(strlen(LoadMgr::$sysModule) + 1);
                    }
                    break;
            }
        }

        return null;
    }

    private static function initCompanyForKeyName($prefixLength = 0)
    {
        $subSysName = LoadMgr::$subSysName;

        IF ($prefixLength > 0)
        {
            if (strlen(LoadMgr::$subSysName) < $prefixLength + 1)
            {
                return null;
            }

            $subSysName = substr(LoadMgr::$subSysName, $prefixLength);
        }

        $exist = false;
        $keyName = null;

        if (CompanySession::exist())
        {
            $info = CompanySession::get();

            if (!empty($info['key_name'])
                && $info['key_name'] == $subSysName)
            {
                $keyName = $subSysName;
                $exist = true;
            }
            else
            {
                CompanySession::clear();
            }
        }

        if (empty($exist))
        {
            $model = CompanyModel::getOne(['key_name' => $subSysName, 'is_enable' => 1]);

            if (empty($model))
            {
                throw new NotFoundHttpException();
            }

            CompanySession::set($model->toArray(['id', 'key_name', 'token', 'name', 'full_name',
                'en_name', 'en_short_name', 'client_id']));

            $keyName = $model->key_name;
        }

        return $keyName;
    }
}