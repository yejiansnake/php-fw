<?php

namespace app\modules\api\admin\controllers;

use Yii;

class HomeController extends BaseController
{
    public function actionTest()
    {
        $a['a']['b'] = (float)50.4;
        $a['a']['c'] = (float)51.4;
        $a['a']['d'] = (float)54.4;
        $a['a']['e'] = (float)40.4;

        $a = $this->multi_array_sort($a);
        print_r($a);
//        print_r($fruits);
        exit;
    }


    private function multi_array_sort($arr, $shortKey, $short = SORT_DESC, $shortType = SORT_REGULAR)
    {
        foreach ($arr as $key => $data)
        {
            $name[$key] = $data[$shortKey];
        }
        array_multisort($name, $shortType, $short, $arr);
        return $arr;
    }

}