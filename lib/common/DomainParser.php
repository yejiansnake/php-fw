<?php

/**
 * 域名解析器
 * User: yejian
 * Date: 2016/2/24
 * Time: 11:32
 */

namespace app\lib\common;

class DomainParser
{
    //域名等级
    const LEVEL_TOP = 0;
    const LEVEL_1 = 1;
    const LEVEL_2 = 2;
    const LEVEL_3 = 3;
    const LEVEL_4 = 4;
    const LEVEL_5 = 5;
    const LEVEL_6 = 6;
    const LEVEL_7 = 7;
    const LEVEL_8 = 8;
    const LEVEL_9 = 9;

    //原始域名
    private $sourceDomain = '';
    //域名数组
    private $domainArray = array();

    /**
     * 构造函数
     * @param $httpDomain string 域名地址(可以包含端口)
     * @return
     */
    public function __construct($httpDomain = null)
    {
        if (!empty($httpDomain))
        {
            $this->load($httpDomain);
        }
    }

    /**
     * 加载域名字符串
     * @param $httpDomain string 域名地址(可以包含端口)
     * @return bool 成功与否
     */
    public function load($httpDomain)
    {
        $this->resetData();
        return $this->parser($httpDomain);
    }

    /**
     * 获取域名（不包含端口）
     * @return string 域名
     */
    public function getSourceDomain()
    {
        return $this->sourceDomain;
    }

    /**
     * 获取域名有效的层级数
     * @return string 域的层级数
     */
    public function getValidCount()
    {
        return count($this->domainArray);
    }

    /**
     * 获取某层域的名称
     * @param $level int 域层级(顶级域名为0:LEVEL_TOP,)
     * @return string 对应层级的域名称
     */
    public function getName($level)
    {
        if (count($this->domainArray) <= $level)
        {
            return '';
        }

        return $this->domainArray[$level];
    }

    /**
     * 解析域名
     * @param $httpDomain string 域名
     * @return bool 成功与否
     */
    private function parser($httpDomain)
    {
        //m.test.bbb.zzzzz.com:60080
        //也可用正则表达式直接取
        $sourceDomain = explode(':', $httpDomain)[0];   //过滤掉端口号
        if (empty($sourceDomain))
        {
            return false;
        }

        //如果为localhost也不处理
        if ($sourceDomain == 'localhost')
        {
            return false;
        }

        //过滤IP
        $pattern = '/\d+\.\d+\.\d+\.\d+/';
        if (preg_match($pattern, $sourceDomain))
        {
            return false;
        }

        //处理
        $domainAarray = explode('.', $sourceDomain);
        $this->domainArray = array_reverse($domainAarray);
        $this->sourceDomain = $sourceDomain;
        return true;
    }

    /**
     * 重置成员数据
     */
    private function resetData()
    {
        $this->sourceDomain = '';
        $this->domainArray = array();
    }
}