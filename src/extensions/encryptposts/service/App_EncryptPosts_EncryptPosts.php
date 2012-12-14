<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('EXT:encryptposts.service.dm.App_EncryptPosts_EncryptPostsDm');
/**
 * App_EncryptPosts_Encryptposts - 数据服务接口
 *
 * @author fengxiao <xiao.fengx@alibaba-inc.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_EncryptPosts_EncryptPosts {
	
	public function add(App_EncryptPosts_EncryptPostsDm $dm) {
		if (true !== ($r = $dm->beforeAdd())) return $r;
		return $this->_loadDao()->add($dm->getData());
	}
	
	public function get($tid) {
		return $this->_loadDao()->get($tid);
	}
	
	public function delete($tid) {
		return $this->_loadDao()->delete($tid);
	}
	
	private function _loadDao() {
		return Wekit::loadDao('EXT:encryptposts.service.dao.App_EncryptPosts_EncryptPostsDao');
	}
}

?>