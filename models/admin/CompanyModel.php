<?php

namespace app\models\admin;

use app\lib\bl\Enum;
use Yii;
use app\lib\bl\LogMgr;

class CompanyModel extends BaseModel
{
    protected static $isNoDelete = true;

    protected static $whereFields = ['id', 'name', 'en_short_name', 'key_name',
        'token', 'client_id', 'is_enable', 'auth_api_id'];

    protected static $whereLikeFields = ['like_name' => 'name'];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'key_name'], 'required'],
            [['is_enable', 'created_by', 'updated_by'], 'integer'],
            [['expires_at', 'created_at', 'updated_at', 'deleted_at'], 'safe'],
            [['name', 'en_name', 'full_name'], 'string', 'max' => 128],
            [['en_short_name', 'key_name', 'token', 'contact_name', 'contact_phone', 'contact_mail'], 'string', 'max' => 64],
            [['key_name'], 'unique']
        ];
    }

    public function load($data, $formName = null)
    {
        if (!empty($data['expires_at']))
        {
            $this->resetDateTime($data, 'expires_at');
        }

        if (isset($data['is_enable']))
        {
            $data['is_enable'] = empty($data['is_enable']) ? 0 : 1;
        }

        if (isset($data['key_name']))
        {
            $data['key_name'] = strtolower($data['key_name']);
        }

        parent::load($data, $formName);
    }

    public static function getKeyName($companyID)
    {
        $model = self::getOne(['id' => $companyID]);

        if (empty($model))
        {
            return null;
        }

        return $model->key_name;
    }

    public static function getCompanyDbName($keyName)
    {
        return APP_SYS_NAME . '_' . $keyName;
    }

    public static function getCompanyDbObject($keyName)
    {
        $dbName = self::getCompanyDbName($keyName);
        $clientDB = \Yii::$app->params['client']['db'];

        $pwd = $clientDB['pwd'];

        $dbConfig = [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$clientDB['ip']};port={$clientDB['port']};dbname={$dbName}",
            'username' => $clientDB['user'],
            'password' => $pwd,
            'charset' => 'utf8mb4',
        ];

        return \Yii::createObject($dbConfig);
    }

    public static function createCompanyDb($keyName)
    {
        $exe = Yii::getAlias('@app') . "/db/init.sh";
        $sqlTpl = Yii::getAlias('@app') . "/db/client.sql";

        if (!file_exists($exe))
        {
            LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR, "初始化脚本未找到:{$exe}");
            return false;
        }

        if (!file_exists($sqlTpl))
        {
            LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR, "SQL初始化脚本未找到:{$sqlTpl}");
            return false;
        }

        $createDbConfig = Yii::$app->params['client']['create_db'];
        $clientDB = \Yii::$app->params['client']['db'];

        $dbInfo = [
            'name' => self::getCompanyDbName($keyName),
            'user' => $clientDB['user'],
            'pwd' => $clientDB['pwd'],
        ];

        $cmd = "/bin/sh {$exe} {$createDbConfig['ip']} {$createDbConfig['user']} {$createDbConfig['pwd']} {$dbInfo['name']} {$dbInfo['user']} {$dbInfo['pwd']} {$sqlTpl} 2>&1";
        $out = shell_exec($cmd);
        if (trim($out) != 'ok')
        {
            LogMgr::sys(__METHOD__, LogMgr::LEVEL_ERROR, "脚本执行失败:{$out}");
            return false;
        }

        return true;
    }
}