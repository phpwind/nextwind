<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.srv.threadList.PwThreadDataSource');

/**
 * 帖子列表数据接口 / 普通列表
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwCateThread.php 21191 2012-11-30 06:22:06Z jieyin $
 * @package forum
 */

class PwCateThread extends PwThreadDataSource {
	
	protected $fid;
	protected $forum;
	protected $forbidFids;
	protected $orderby = '';

	public function __construct($forum, $forbidFids = array()) {
		$this->forum = $forum;
		$this->fid = $forum->fid;
		$this->forbidFids = $forbidFids;
	}

	public function setOrderby($order) {
		if ($order == 'postdate') {
			$this->orderby = $order;
		}
	}

	public function getTotal() {
		return $this->_getThreadCateIndexDs()->countNotInFids($this->fid, $this->forbidFids);
	}

	public function getData($limit, $offset) {
		$tids = $this->_getThreadCateIndexDs()->fetchNotInFid($this->fid, $this->forbidFids, $limit, $offset, $this->orderby);
		$threaddb = $this->_getThreadDs()->fetchThread($tids);
		$threaddb = $this->_sort($threaddb, $tids);
		return $threaddb;
	}

	protected function _getThreadDs() {
		return Wekit::load('forum.PwThread');
	}

	protected function _getThreadCateIndexDs() {
		return Wekit::load('forum.PwThreadCateIndex');
	}

	protected function _sort($data, $sort) {
		$result = array();
		foreach ($sort as $tid) {
			$result[$tid] = $data[$tid];
		}
		return $result;
	}
}