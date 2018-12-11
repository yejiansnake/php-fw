<?php

/*
    规则:
    1.基本的检查首先是判断用户是否登录，当然也是访问检验的首要规则，所以用户必须先登录，否则永远返回false。
    2.所有的管控默认为通行，所以不特殊配置，就表示不需要特殊权限，登录就可以访问。
    3.支持模块下的控制器 和 控制器/行为 2种粒度的权限管理。
    4.权限条件分为 type:用户类型; role:拥有角色; permission:拥有权限; 3个部分进行配置，配置数据都为id Array。
    5.检测顺序: controller ---> action, 如果 controller 检测不过的，直接返回false。
    6.用户授权信息必须满足权限条件，3种类型条件不相关,每种类型条件只需要满足其集合的一个即可，
        但是如果存在多种类型组合的权限条件，就是需要每种类型都满足。


*/

namespace app\lib\common;

use Yii;
use yii\base\ActionFilter;

abstract class BaseAccessFilter extends ActionFilter
{
    const ACCESS_TYPE = 'type';
    const ACCESS_ROLE = 'role';
    const ACCESS_PERMISSION = 'permission';

    //可重载数据成员------------------------------------------------------------------

    //当为 $enable = false 时，不进行任何检测，为来在开发的时候方便测试，默认为 true
    public $enable = true;

    //重载方法------------------------------------------------------------------------

    /*
    功能：获取用户所有权限资源
    例子：
        $userInfo = SessionMgr::CurUser()->get();

        return [self::ACCESS_TYPE => [ $userInfo['type'] ],
            self::ACCESS_ROLE => $userInfo['roles'],
            self::ACCESS_PERMISSION => $userInfo['permissions']
        ];
    */
    abstract public function getUserResource();

    /*
    功能：获取访问规则
    例子：
        return [
            'controller' => [
                'company' => [                      //控制器名称
                    'type' => [0,1],                //可选
                    'role' => [1,3,5,6],            //可选
                    'permission' => [1,2,3,7,8,11], //可选
                ],
                //......
            ],
            'action' => [
                'company/index' => [                //控制器名称/行为名称
                    'type' => [0,1],
                    'role' => [1,3,5,6],
                    'permission' => [1,2,3,7,8,11],
                ],
                'company/view' => [
                    'type' => [0,1],
                    'role' => [1,3,5,6],
                    'permission' => [1,2,3,7,8,11],
                ],
                //......
            ],
        ];
    */
    abstract public function getRules();

    /*
    事件：验证失败时
    参数:
        $action 当前的action对象
    例子：
        Yii::$app->response->format = Response::FORMAT_JSON;
        throw new UnauthorizedHttpException("没有权限访问");
    */
    abstract public function onUnauthorized($action);

    /*
    事件：验证失败时(没有权限)
    参数:
        $action 当前的action对象
    例子：
        Yii::$app->response->format = Response::FORMAT_JSON;
        throw new ForbiddenHttpException("没有权限访问");
    */
    abstract public function onForbidden($action);
    /*
    功能：判断是否为超级管理
    参数:
        $userResource 从 getUserResource 获取的用户资源信息
    返回值:
        如果是超级管理员，返回 true,并且不受任何权限影响
    */
    public function isSuperAdmin($userResource)
    {
        return false;
    }

    /*
    事件：验证成功时(无需要可以不用重载)
    参数:
        $action 当前的action对象
    */
    public function onAuthorized($action)
    {

    }

    //内部方法------------------------------------------------------------------

    final public function beforeAction($action)
    {
        //var_dump('controller:'. $action->controller->id .' action:'.$action->id);
        //exit();

        if ($this->enable)
        {
            $res = $this->checkAccess($action);
            if ($res)
            {
                $this->onAuthorized($action);
            }
            else
            {
                if (isset($res))
                {
                    $this->onForbidden($action);
                }
                else
                {
                    $this->onUnauthorized($action);
                }
            }
        }

        return parent::beforeAction($action);
    }

    final private function checkAccess($action)
    {
        $userResource = $this->getUserResource();
        if(empty($userResource))
        {
            return null;
        }

        if ($this->isSuperAdmin($userResource))
        {
            return true;
        }

        $rules = $this->getRules();

        //基础信息
        $actionID = $action->id;
        $controllerID = $action->controller->id;

        if (isset($rules['controller']))
        {
            if (!$this->check($rules['controller'], $controllerID, $userResource))
            {
                return false;
            }
        }

        if (isset($rules['action']))
        {
            if (!$this->check($rules['action'], "{$controllerID}/{$actionID}", $userResource))
            {
                return false;
            }
        }

        return true;
    }

    final private function check($checkRule, $checkKey, $userResource)
    {
        if (array_key_exists($checkKey, $checkRule))
        {
            $pmRule = $checkRule[$checkKey];

            if (!$this->checkOne($pmRule, $userResource, self::ACCESS_TYPE))
            {
                return false;
            }

            if (!$this->checkOne($pmRule, $userResource, self::ACCESS_ROLE))
            {
                return false;
            }

            if (!$this->checkOne($pmRule, $userResource, self::ACCESS_PERMISSION))
            {
                return false;
            }
        }

        return true;
    }

    final private function checkOne($pmRule, $userResource, $ruleType)
    {
        if (isset($pmRule[$ruleType]))
        {
            $typeRules = $pmRule[$ruleType];

            if (count($typeRules) > 0)
            {
                if (!isset($userResource[$ruleType]))
                {
                    return false;
                }

//                var_dump($pmRule[$ruleType]);
//                var_dump($userResource[$ruleType]);

                if (count(array_intersect($pmRule[$ruleType], $userResource[$ruleType])) <= 0)
                {
                    return false;
                }

//                var_dump('true');
//                exit();
            }
        }

        return true;
    }
}