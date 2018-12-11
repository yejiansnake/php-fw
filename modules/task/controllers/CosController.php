<?php

namespace app\modules\task\controllers;

use Yii;
use app\lib\bl\LogMgr;
use app\lib\vendor\QCloud\CosClient;
use app\lib\common\DateTimeEx;

class CosController extends BaseController
{
    public function actionReportClear()
    {
        $expiredTime = 7 * 24 * 60 * 60;
        $objCollection = CosClient::getObjectList(['bucket' => CosClient::BUCKET_KEY_REPORT]);

        if (empty($objCollection) || empty($objCollection['Contents']))
        {
            return self::EXIT_CODE_NORMAL;
        }

        foreach ($objCollection['Contents'] as $object)
        {
            if (DateTimeEx::compareNowTime($object['LastModified'], $expiredTime) < 0)
            {
                CosClient::deleteObject([
                    'bucket' => CosClient::BUCKET_KEY_REPORT,
                    'key' => $object['Key']
                ]);

                $objTime = DateTimeEx::toLocal($object['LastModified']);

                LogMgr::cloud(__METHOD__, LogMgr::LEVEL_INFO,
                    "delete report object key:{$object['Key']}, LastModified:{$objTime}");
            }
        }

        return self::EXIT_CODE_NORMAL;
    }
}