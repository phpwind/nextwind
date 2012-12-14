<?php
! defined ( 'ACLOUD_PATH' ) && exit ( 'Forbidden' );

define ( 'FORUM_INVALID_PARAMS', 401 );
define ( 'FORUM_FAVOR_MAX', 402 );
define ( 'FORUM_FAVOR_ALREADY', 403 );
define ( 'FORUM_NOT_EXISTS', 404 );

class ACloudVerCustomizedForum extends ACloudVerCustomizedBase {
	
	/**
	 * 获取版块列表
	 * 
	 * @return array
	 */
	public function getAllForum() {
		$forumDs = $this->_getPwForum ();
		$forumResult = $forumDs->getForumList ( PwForum::FETCH_MAIN | PwForum::FETCH_STATISTICS );
		if ($forumResult instanceof PwError)
			return $this->buildResponse ( - 1, $forumResult->getError () );
		$cates = $forums = $subForums = $secondSubForums = array ();
		$count = 0;
		foreach ( $forumResult as $v ) {
			if ($v ['type'] == 'category') {
				$cates [$v ['fid']] = array ('fid' => $v ['fid'], 'forumname' => strip_tags ( $v ['name'] ), 'type' => $v ['type'], 'todaypost' => '' );
			} elseif ($v ['type'] == 'forum') {
				Wind::import('SRV:forum.bo.PwForumBo');
				$pwforum = new PwForumBo($v['fid'], true);
				if ($pwforum->allowVisit(Wekit::getLoginUser()) !== true) continue;
				$forums [$v ['parentid']] [$v ['fid']] = array ('fid' => $v ['fid'], 'forumname' => strip_tags ( $v ['name'] ), 'type' => $v ['type'], 'todaypost' => $v ['todayposts'] );
			} elseif ($v ['type'] == 'sub') {
				$subForums [$v ['parentid']] [$v ['fid']] = array ('fid' => $v ['fid'], 'forumname' => strip_tags ( $v ['name'] ), 'type' => $v ['type'], 'todaypost' => $v ['todayposts'] );
			} elseif ($v ['type'] == 'sub2') {
				$secondSubForums [$v ['parentid']] [$v ['fid']] = array ('fid' => $v ['fid'], 'forumname' => strip_tags ( $v ['name'] ), 'type' => $v ['type'], 'todaypost' => $v ['todayposts'] );
			}
			$count ++;
		}
		$result = array ();
		foreach ( $cates as $k => $v ) {
			$v ['child'] = isset ( $forums [$k] ) ? $this->_buildForums ( $forums [$k], $subForums, $secondSubForums ) : array ();
			$result [] = $v;
		}
		return $this->buildResponse ( 0, array ('count' => $count, 'forums' => $result ) );
	}
	
	/**
	 * 根据版块id获取版块列表
	 * 
	 * @param int $fid
	 * @return array
	 */
	public function getForumByFid($fid) {
		$fid = intval ( $fid );
		if ($fid < 1)
			return $this->buildResponse ( FORUM_INVALID_PARAMS );
		$result = $this->_getPwForum ()->getForum ( $fid, PwForum::FETCH_MAIN | PwForum::FETCH_STATISTICS );
		if (result instanceof PwError)
			return $this->buildResponse ( - 1, $result->getError () );
		return $this->buildResponse ( 0, array ('forum' => array ('fid' => $result ['fid'], 'forumname' => $result ['name'], 'todaypost' => $result ['todayposts'] ) ) );
	}
	
	/**
	 * 根据版块id获取子版块
	 * 
	 * @param int $fid
	 * @return array
	 */
	public function getChildForumByFid($fid) {
		$fid = intval ( $fid );
		if ($fid < 1)
			return $this->buildResponse ( FORUM_INVALID_PARAMS );
		$forumService = $this->_getFroumService ();
		$map = $forumService->getForumMap ();
		$forumList = $forumService->getForumsByLevel ( $fid, $map );
		if ($forumList instanceof PwError)
			return $this->buildResponse ( - 1, $forumList->getError () );
		$forums = $subForums = $secondSubForums = array ();
		$count = 0;
		foreach ( $forumList as $v ) {
			$statistics = $this->_getPwForum ()->getForum ( $v ['fid'], PwForum::FETCH_STATISTICS );
			if ($v ['type'] == 'forum') {
				//TODO 用户版块访问权限
				$forums [$v ['fid']] = array ('fid' => $v ['fid'], 'forumname' => strip_tags ( $v ['name'] ), 'type' => $v ['type'], 'todaypost' => $statistics ['todayposts'] );
			} elseif ($v ['type'] == 'sub') {
				$subForums [$v ['parentid']] [$v ['fid']] = array ('fid' => $v ['fid'], 'forumname' => strip_tags ( $v ['name'] ), 'type' => $v ['type'], 'todaypost' => $statistics ['todayposts'] );
			} elseif ($v ['type'] == 'sub2') {
				$secondSubForums [$v ['parentid']] [$v ['fid']] = array ('fid' => $v ['fid'], 'forumname' => strip_tags ( $v ['name'] ), 'type' => $v ['type'], 'todaypost' => $statistics ['todayposts'] );
			}
			$count ++;
		}
		$result = array ();
		foreach ( $forums as $k => $v ) {
			$v ['child'] = isset ( $subForums [$k] ) ? $this->_fetchSubForum ( $subForums [$k], $secondSubForums ) : array ();
			$result [] = $v;
		}
		return $this->buildResponse ( 0, array ('count' => $count, 'forums' => $result ) );
	}
	
	/**
	 * 
	 * 获取指定版块中的主题分类信息
	 *
	 * @param int $fid
	 * @return array
	 */
	public function getTopicType($fid){
		$topicTypes = $this->_getTopictypeDs()->getTopicTypesByFid($fid);
		$result = array();
		foreach ($topicTypes['all_types'] as $k => $v){
			$result[$k] = $v;
			$result[$k]['upid'] = $v['parentid'];
		}
		return $this->buildResponse ( 0, array('info' => $result));
	}
	
	
	private function _buildForums($forums, $subForums, $secondSubForums) {
		$result = array ();
		foreach ( $forums as $fid => $forum ) {
			$forum ['child'] = (isset ( $subForums [$fid] ) && $subForums [$fid]) ? $this->_buildSubForums ( $subForums [$fid], $secondSubForums ) : array ();
			$result [] = $forum;
		}
		return $result;
	}
	
	private function _buildSubForums($subForums, $secondSubForums) {
		$result = array ();
		foreach ( $subForums as $fid => $subForum ) {
			$subForum ['child'] = (isset ( $secondSubForums [$fid] ) && $secondSubForums [$fid]) ? $secondSubForums [$fid] : array ();
			$result [] = $subForum;
		}
		return $result;
	}
	
	private function _fetchSubForum($subForums, $secondSubForums) {
		$result = array ();
		foreach ( $subForums as $fid => $subForum ) {
			$subForum ['child'] = (isset ( $secondSubForums [$fid] ) && $secondSubForums [$fid]) ? $secondSubForums [$fid] : array ();
			$result [$fid] = $subForum;
		}
		return $result;
	}
	
	private function _getPwForum() {
		return Wekit::load ( 'SRV:forum.PwForum' );
	}
	
	private function _getFroumService() {
		return Wekit::load ( 'SRV:forum.srv.PwForumService' );
	}
	
	private function _getTopictypeDs() {
		return Wekit::load('forum.PwTopicType');
	}
}