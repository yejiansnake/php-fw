<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/2/29
 * Time: 11:09
 */

namespace app\lib\common;

final class PaginationHelper
{
    protected function __construct()
    {

    }

    //传入GET参数，协议好_page_size和_page参数
    public static function getPagination($params){
        $pageSize = 20;
        if(isset($params['_page_size']) && $params['_page_size'] == -1) {
            $pagination = false;
        } else {
            isset($params['_page'])? $page = $params['_page']: $page = 1;
            if (isset($params['_page_size']) && $params['_page_size'] > 0) {
                $pageSize = $params['_page_size'];
            }
            $pagination = [
                'pageSize' => $pageSize,
                'page' => $page - 1,
            ];
        }
        return $pagination;
    }

    //传入pagination结构和query， 返回meta结构
    public static function getPaginationMeta($pagination, $query){
        $count_query = clone $query;

        $_meta = [];
        $_meta['total_count'] = $count_query->count();
        if($pagination){
            $_meta['per_page'] = $pagination['page_size'];
            $_meta['current_page'] = $pagination['page'] + 1;
            $_meta['page_count'] = ceil($_meta['total_count'] / $_meta['per_page']);
        }
        return $_meta;
    }
}