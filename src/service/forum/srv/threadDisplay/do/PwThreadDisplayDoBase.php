<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 帖子发布相关服务
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwThreadDisplayDoBase.php 21409 2012-12-06 11:30:15Z jieyin $
 * @package forum
 */

abstract class PwThreadDisplayDoBase {

	public function check() {
		return true;
	}

	public function bulidRead($read) {
		return $read;
	}

	public function bulidUsers($users) {
		return $users;
	}
	
	/**
	 * 在这里输出插件内容 (位置：帖子内容上方)
	 */
	public function createHtmlBeforeContent($read) {

	}
	
	/**
	 * 在这里输出插件内容 (位置：帖子内容下方)
	 */
	public function createHtmlAfterContent($read) {

	}
	
	/**
	 * 在这里输出插件内容 (位置：用户信息下方)
	 */
	public function createHtmlAfterUserInfo($user, $read) {
		
	}
}