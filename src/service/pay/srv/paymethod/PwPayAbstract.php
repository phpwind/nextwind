<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 在线支付
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwPayAbstract.php 7431 2012-04-06 01:54:39Z jieyin $
 * @package forum
 */

abstract class PwPayAbstract {

	public function check() {
		return true;
	}

	abstract public function createOrderNo();

	abstract public function getUrl(PwPayVo $vo);
}