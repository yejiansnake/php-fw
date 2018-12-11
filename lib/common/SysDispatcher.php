<?php

/**
 * 系统分配器
 * User: yejian
 * Date: 2016/2/25
 * Time: 11:32
 */

namespace app\lib\common;

class SysDispatcher
{
	private $routes = array();

	/**
	 * 初始化
	 * @param $routes array (dict) 字典（key:route 正则表达式, value: controller/action)
	 */
	public function __construct(array $routes = null)
	{
		if (is_array($routes))
		{
			$this->routes = $routes;
			return;
		}
	}

	/**
	 * 设置路由规则
	 * @param $routes array (dict) 字典（key:route 正则表达式, value: controller/action)
	 * @return bool 成功与否
	 */
	public function setRoutes(array $routes)
	{
		if (is_array($routes))
		{
			$this->routes = $routes;
			return true;
		}

		return false;
	}

	/**
	 * 添加路由规则
	 * @param $patternDomain string 路由的正则表达式
	 * @param $route string 控制器和行为
	 * @return bool 成功与否
	 */
	public function addRoute($patternDomain, $route)
	{
		if (!is_string($patternDomain)
			|| !is_string($route)
			|| empty($patternDomain)
			|| empty($route))
		{
			return false;
		}

		$this->routes[] = [
			$patternDomain => $route,
		];

		return true;
	}

	/**
	 * 添加路由规则
	 * @param $routes array (dict) 字典（key:route 正则表达式, value: controller/action)
	 * @return bool 成功与否
	 */
	public function addRoutes(array $routes)
	{
		if (!is_array($routes))
		{
			return false;
		}

		$this->routes[] = $routes;

		return true;
	}

	/**
	 * 添加路由规则
	 * @param $domain string 域名
	 * @return string 成功时返回controller/action,匹配不到则返回空字符串
	 */
	public function getRoute($domain)
	{
		if (!is_string($domain) || empty($domain))
		{
			return '';
		}

		foreach ($this->routes as $key => $value)
		{
			if (0 < preg_match('/'.$key.'/', $domain))
			{
				return $value;
			}
		}

		return '';
	}
}