<?php

/**
 * 帖子基础dao服务
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwPostExpandDao.php 14625 2012-07-25 02:47:14Z jinlong.panjl $
 * @package forum
 */

class PwPostExpandDao extends PwBaseDao {
	
	protected $_table = 'bbs_posts';
	protected $_pk = 'pid';
	protected $_dataStruct = array('pid', 'fid', 'tid', 'ischeck', 'useubb', 'aids', 'subject', 'content', 'created_time', 'created_username', 'created_userid', 'created_ip', 'modified_time', 'modified_username', 'modified_userid', 'modified_ip');

	public function countUserPostByFidAndTime($fid, $time, $limit) {
		$sql = $this->_bindSql('SELECT created_userid,COUNT(*) AS count FROM %s WHERE fid=? AND created_time>? GROUP BY created_userid ORDER BY count DESC %s', $this->getTable(), $this->sqlLimit($limit));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($fid, $time), 'created_userid');
	}

	public function countPostsByFid() {
		$sql = $this->_bindTable('SELECT fid,COUNT(*) AS sum FROM %s WHERE disabled=0 GROUP BY fid');
		$rst = $this->getConnection()->query($sql);
		return $rst->fetchAll('fid');
	}

	public function countDisabledPostByUid($uid) {
		$sql = $this->_bindTable('SELECT COUNT(*) FROM %s WHERE created_userid=? AND disabled<2');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue(array($uid));
	}

	public function getDisabledPostByUid($uid, $limit, $offset) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE created_userid=? AND disabled<2 ORDER BY created_time DESC %s', $this->getTable(), $this->sqlLimit($limit, $offset));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid), 'pid');
	}
}