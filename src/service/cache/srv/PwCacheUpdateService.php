<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 全局缓存更新服务
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwCacheUpdateService.php 19909 2012-10-19 08:48:18Z jieyin $
 * @package forum
 */

class PwCacheUpdateService {
	
	/**
	 * 更新所有缓存
	 */
	public function updateAll() {
		$this->updateConfig();
		$this->updateGroup();
		$this->updateMedal();
	}
	
	/**
	 * 更新全局配置 config
	 */
	public function updateConfig() {
		$vkeys = array('site', 'credit', 'bbs', 'attachment', 'components', 'seo', 'nav');
		$array = Wekit::load('config.PwConfig')->fetchConfig($vkeys);
		$config = array();
		foreach ($vkeys as $key => $value) {
			$config[$value] = array();
		}
		foreach ($array as $key => $value) {
			$config[$value['namespace']][$value['name']] = $value['vtype'] != 'string' ? unserialize($value['value']) : $value['value'];
		}
		Wekit::cache()->set('config', $config);
	}
	
	/**
	 * 更新用户组缓存
	 */
	public function updateGroup() {
		$srv = Wekit::load('usergroup.srv.PwUserGroupsService');
		$srv->updateLevelCache();
		$srv->updateGroupRightCache();
		$srv->updateGroupCache();
	}
	
	/**
	 * 更新勋章缓存
	 */
	public function updateMedal() {
		Wekit::load('SRV:medal.srv.PwMedalService')->updateCache();
	}
}