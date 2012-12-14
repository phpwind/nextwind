<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:process.iPwGleanDoHookProcess');

/**
 * 帖子删除扩展服务接口--虚拟删除到回收站
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDeleteArticleDoForumUpdate.php 13302 2012-07-05 03:45:43Z jieyin $
 * @package forum
 */

class PwDeleteArticleDoForumUpdate extends iPwGleanDoHookProcess {
	
	public $record = array();
	
	public function gleanData($value) {
		if ($value['disabled'] != 2) {
			$fid = $value['fid'];
			isset($this->record[$fid]) || $this->record[$fid] = array('topic' => 0, 'replies' => 0);
			$this->record[$fid]['replies'] += $value['replies'];
			$value['disabled'] == 0 && $this->record[$fid]['topic']++;
		}
	}

	public function run($ids) {
		$srv = Wekit::load('forum.srv.PwForumService');
		foreach ($this->record as $fid => $value) {
			$srv->updateStatistics($fid, -$value['topic'], -$value['replies']);
		}
	}
}