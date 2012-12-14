<?php
Wind::import('WINDID:library.WindidUtility');
Wind::import('WINDID:library.WindidError');
Wind::import('WINDID:service.client.bo.WindidClientBo');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: OpenBaseApi.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */
class OpenBaseApi {
	
	protected function getInput($key = null, $method = 'post') {
		switch (strtolower($method)) {
			case 'get':
				$value = Wind::getApp()->getRequest()->getGet($key);
				break;
			case 'post':
				$value = Wind::getApp()->getRequest()->getPost($key);
				break;
			case 'cookie':
				$value = Wind::getApp()->getRequest()->getCookie($key);
				break;
			default:
				$value = Wind::getApp()->getRequest()->getRequest($key);
		}
		return $value;
	}
	
	public function __call($method, $args) {
		return WindidError::METHOD_ERROR;
	}
}
?>