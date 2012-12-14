<?php

Wind::import('SRV:tag.srv.action.PwTagAction');

class PwTagThreads extends PwTagAction{

	/**
	 * (non-PHPdoc)
	 * @see PwTagAction::getContents()
	 */
	public function getContents($ids){
		$threads = $this->_getThreadDs()->fetchThread($ids,PwThread::FETCH_ALL);
		if ($threads) {
			$fids = $array = array();
			foreach ($threads as $v) {
				$fids[] = $v['fid'];
			}
			$forums = $this->_getForumDs()->fetchForum($fids);
			foreach ($threads as $k=>$v) {
				if ($v['disabled'] > 0) continue;
				$v['forum_name'] = $forums[$v['fid']]['name'];
				$v['created_time_auto'] = pw::time2str($v['created_time'],'auto');
				$v['type_id'] = $forums[$v['fid']]['name'];
				$array[$k] = $v;
			}
		}
		
		return $array;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return PwThread
	 */
	private function _getThreadDs(){
		return Wekit::load('forum.PwThread');
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @return PwForum
	 */
	private function _getForumDs(){
		return Wekit::load('forum.PwForum');
	}
}