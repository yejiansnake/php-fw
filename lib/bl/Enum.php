<?php
/**
 * 系统的枚举信息
 */

namespace app\lib\bl;

use app\lib\common\EnumHelper;

class Enum extends EnumHelper
{
    //------------------------------------------------------------------------------------------
    const EXCEPTION_CODE_SILENT = 1000;  //异常信息不显示在alert中
    const EXCEPTION_CODE_WX = 1001;  //需要先在微信网页授权登录

    //------------------------------------------------------------------------------------------
    //ADMIN 系统属性表

    //全局基础配置
    const ADMIN_PM_TYPE_BASE = 1;

    //数据信息
    const ADMIN_PM_TYPE_DATA = 2;
    const ADMIN_PM_TYPE_DATA_ID_WECHAT_TOKEN = 1;           //微信 access_token
    const ADMIN_PM_TYPE_DATA_ID_WECHAT_JS_TICKET = 2;       //微信 js_ticket

    const ADMIN_PM_TYPE_DATA_ID_WECHAT_COMP_TICKET = 3;         //微信第三方平台 vendor_ticket
    const ADMIN_PM_TYPE_DATA_ID_WECHAT_COMP_ACCESS_TOKEN = 4;   //微信第三方平台 vendor_token

    const ADMIN_PM_TYPE_DATA_ID_WECHAT_MENU = 6;            //微信 自定义菜单功能

    const ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_TOKEN = 101;                    //企业微信 corp_access_token
    const ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_JS_TICKET = 102;                //企业微信 corp_js_ticket

    const ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_SUITE_TICKET = 103;             //企业微信套件 suite_ticket
    const ADMIN_PM_TYPE_DATA_ID_CORP_WECHAT_SUITE_ACCESS_TOKEN = 104;       //企业微信套件 suite_access_token

    //------------------------------------------------------------------------------------------
    //Client_Wx_Token 客户微信授权TOKEN 表

    const CLIENT_WX_TOKEN_TYPE_ACCESS_TOKEN = 1;        //微信客户 access_token
    const CLIENT_WX_TOKEN_TYPE_JS_TICKET = 2;           //微信客户 js_ticket

    const CLIENT_WX_TOKEN_TYPE_CORP_ACCESS_TOKEN = 11;  //企业微信客户 access_token
    const CLIENT_WX_TOKEN_TYPE_CORP_JS_TICKET = 12;     //企业微信客户 js_ticket

    //------------------------------------------------------------------------------------------
    //Client 系统属性表

    const CLIENT_PM_TYPE_BASE = 1;
    const CLIENT_PM_TYPE_BASE_ID_WECHAT_SHARE = 1;   //wx share
    const CLIENT_PM_TYPE_BASE_ID_WECHAT_AUTHORIZE = 2;   //wx authorize


    const CLIENT_PM_TYPE_SETTING = 2;


    //------------------------------------------------------------------------------------------


    //方法

    public static function get()
    {
        return [
        ];
    }
}
