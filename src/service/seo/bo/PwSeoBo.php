<?php
/**
 * 1、设置seo信息，可以直接输入文字，也支持参数选择；
 * 2、定位到输入框，可以弹出可以使用的参数，选择后显示到输入框；
 * 3、可以使用的参数：
 * 论坛首页：站点名称{sitename}
 * 帖子列表：站点名称{sitename}、版块名称{forumname}、版块简介{forumdescription}
 * 帖子阅读页：站点名称{sitename}、版块名称{forumname}、帖子标题{title}、帖子摘要{description}、帖子主题分类{classification}、标签{tags}
 * 
 * 显示逻辑：
 * 以帖子列表页为例：
 * 如果版块设置了seo，则显示版块seo;
 * 如果帖子列表页设置了，则显示帖子列表页的;
 * 最后如果都没有，显示全局seo
 * 
 * 默认数据：
 * 考虑当后台没有设置任何seo信息时的默认显示数据。
 * 先确定论坛的三大页面，其他的页面由各个应用考虑。(此处具体见service.seo.conf)
 * 论坛导航页：
 * title：论坛名称
 * keyword：空
 * description：空
 * 主题列表页：
 * title：版块名称_论坛名称
 * keyword：空
 * description：版块简介。如果没有设置，留空
 * 帖子阅读页：
 * title：帖子标题_版块名称_论坛名称
 * keyword：空
 * description：帖子摘要，截取内容前100字节
 * 
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package service.seo.bo
 */
class PwSeoBo {
	/**
	 * 全局seo格式，即页面也没有设置默认的seo格式的情况
	 *
	 * @var array
	 */
	protected static $defaultSeo = array(
		'title' => '{sitename}', 
		'description' => '{sitename}', 
		'keywords' => '{sitename}');
	protected static $seo = array();
	protected static $codeData = array();
	protected static $default = array();

	/**
	 * 初始化页面的seo格式
	 *
	 * 显示逻辑：
	 * 以帖子列表页为例：
	 * 如果版块设置了seo，则显示版块seo;
	 * 如果帖子列表页设置了，则显示帖子列表页的;
	 *
	 * @param string $mod        	
	 * @param string $page        	
	 * @param string $param        	
	 */
	public static function init($mod, $page, $param = '0') {
		self::$default || self::$default = Wekit::load('APPS:seo.service.PwSeoExtends')->getDefaultSeoByPage(
			$page, $mod);
		/*
		 * 显示逻辑： 参数为0表示页面，参数不为0表示子页面。例如版块列表页参数为0，具体某个版块的页面参数为fid
		 * 1、参数为0，显示自定义的，否则显示默认值 2、参数不为0，显示自定义的，没有则显示参数为0的自定义的，也没有就显示参数为0的默认值
		 */
		if ($param != '0') {
			list($seo, $seo_0) = array(
				self::_seoService()->getByModAndPageAndParamWithCache($mod, $page, $param), 
				self::_seoService()->getByModAndPageAndParamWithCache($mod, $page, 0));
			self::$seo = self::_choose($seo, $seo_0, self::$default);
		} else {
			$result = self::_seoService()->getByModAndPageAndParamWithCache($mod, $page, '0');
			self::$seo = self::_choose($result, false, self::$default);
		}
	}

	/**
	 * 设置占位符的值
	 *
	 * @param string $code        	
	 * @param string $value        	
	 */
	public static function set($code, $value = '') {
		if (is_array($code))
			self::$codeData = array_merge(self::$codeData, $code);
		else
			self::$codeData[$code] = $value;
	}

	/**
	 * 返回seo值
	 *
	 * @return array
	 */
	public static function getData() {
		empty(self::$seo) && self::$seo = self::$defaultSeo;
		foreach (self::$seo as $k => &$v)
			$v = strip_tags(trim(strtr($v, self::$codeData), '-_ '));
		return self::$seo;
	}

	/**
	 * 此接口仅供无后台管理模式的seo值设置
	 *
	 * @param string $title        	
	 * @param string $keywords        	
	 * @param string $description        	
	 */
	public static function setCustomSeo($title, $keywords, $description) {
		if ($title || $keywords || $description) {
			self::$seo = array(
				'title' => $title, 
				'keywords' => $keywords, 
				'description' => $description);
		}
	}

	public static function setDefaultSeo($title, $keywords, $description) {
		self::$default = array(
			'title' => $title, 
			'keywords' => $keywords, 
			'description' => $description);
	}

	/**
	 *
	 * @return PwSeoService
	 */
	private static function _seoService() {
		return Wekit::load('seo.srv.PwSeoService');
	}

	private static function _choose($option1, $option2 = false, $default) {
		$tmp = array();
		if ($option2 !== false) {
			$tmp['title'] = $option1['title'] ? $option1['title'] : ($option2['title'] ? $option2['title'] : $default['title']);
			$tmp['description'] = $option1['description'] ? $option1['description'] : ($option2['description'] ? $option2['description'] : $default['description']);
			$tmp['keywords'] = $option1['keywords'] ? $option1['keywords'] : ($option2['keywords'] ? $option2['keywords'] : $default['keywords']);
		} else {
			$tmp['title'] = $option1['title'] ? $option1['title'] : $default['title'];
			$tmp['description'] = $option1['description'] ? $option1['description'] : $default['description'];
			$tmp['keywords'] = $option1['keywords'] ? $option1['keywords'] : $default['keywords'];
		}
		return $tmp;
	}
}

?>