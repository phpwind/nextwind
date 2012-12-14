<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('SRC:library.base.PwBaseDao');

/**
 * App_EncryptPosts_EncryptpostsDao - dao
 *
 * @author fengxiao <xiao.fengx@alibaba-inc.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_EncryptPosts_EncryptPostsDao extends PwBaseDao {
	
	/**
	 * table name
	 */
	protected $_table = 'app_encryptposts';
	/**
	 * primary key
	 */
	protected $_pk = 'tid';
	/**
	 * table fields
	 */
	protected $_dataStruct = array('tid','token');
	
	public function add($fields) {
		return $this->_add($fields, true);
	}
	
	public function update($tid, $fields) {
		return $this->_update($id, $fields);
	}
	
	public function delete($tid) {
		return $this->_delete($tid);
	}
	
	public function get($tid) {
		return $this->_get($tid);
	}
}

?>