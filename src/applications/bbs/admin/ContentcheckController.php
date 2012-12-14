<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('ADMIN:library.AdminBaseController');

/**
 * 帖子审核管理
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: ContentcheckController.php 21335 2012-12-05 03:39:26Z jieyin $
 * @package forum
 */

class ContentcheckController extends AdminBaseController {

	public function run() {
		
		$page = intval($this->getInput('page'));
		list($author, $fid, $createdTimeStart, $createdTimeEnd) = $this->getInput(array('author', 'fid', 'created_time_start', 'created_time_end'));

		$page < 1 && $page = 1;
		$perpage = 20;
		list($start, $limit) = Pw::page2limit($page, $perpage);

		Wind::import('SRV:forum.vo.PwThreadSo');
		$so = new PwThreadSo();
		$so->setDisabled(1)->orderbyCreatedTime(0);
		$url = array();
		
		if ($author) {
			$so->setAuthor($author);
			$url['author'] = $author;
		}
		if ($fid) {
			$so->setFid($fid);
			$url['fid'] = $fid;
		}
		if ($createdTimeStart) {
			$so->setCreateTimeStart(Pw::str2time($createdTimeStart));
			$url['created_time_start'] = $createdTimeStart;
		}
		if ($createdTimeEnd) {
			$so->setCreateTimeEnd(Pw::str2time($createdTimeEnd));
			$url['created_time_end'] = $createdTimeEnd;
		}

		$count = Wekit::load('forum.PwThread')->countSearchThread($so);
		$threaddb = Wekit::load('forum.PwThread')->searchThread($so, $limit, $start, PwThread::FETCH_ALL);

		$this->setOutput($threaddb, 'threadb');
		$this->setOutput(Wekit::load('forum.srv.PwForumService')->getForumList(), 'forumlist');
		$this->setOutput(Wekit::load('forum.srv.PwForumService')->getForumOption(), 'option_html');

		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($count, 'count');
		$this->setOutput($url, 'url');
	}
	
	public function doPassThreadAction() {

		$tid = $this->getInput('tid');
		if (empty($tid)) {
			$this->showError('operate.select');
		}
		!is_array($tid) && $tid = array($tid);
		
		Wind::import('SRV:forum.srv.dataSource.PwFetchTopicByTid');
		Wind::import('SRV:forum.srv.operation.PwPassTopic');

		$service = new PwPassTopic(new PwFetchTopicByTid($tid));
		$service->execute();

		$this->showMessage('success');
	}

	public function doDeleteThreadAction() {

		$tid = $this->getInput('tid');
		if (empty($tid)) {
			$this->showError('operate.select');
		}
		!is_array($tid) && $tid = array($tid);

		Wind::import('SRV:forum.srv.operation.PwDeleteTopic');
		Wind::import('SRV:forum.srv.dataSource.PwFetchTopicByTid');
		$deleteTopic = new PwDeleteTopic(new PwFetchTopicByTid($tid), new PwUserBo($this->adminUser->getUid()));
		$deleteTopic->setIsDeductCredit(1)->execute();

		$this->showMessage('success');
	}

	public function replyAction() {
		
		$page = intval($this->getInput('page'));
		list($author, $fid, $createdTimeStart, $createdTimeEnd) = $this->getInput(array('author', 'fid', 'created_time_start', 'created_time_end'));

		$page < 1 && $page = 1;
		$perpage = 20;
		list($start, $limit) = Pw::page2limit($page, $perpage);

		Wind::import('SRV:forum.vo.PwPostSo');
		$so = new PwPostSo();
		$so->setDisabled(1)->orderbyCreatedTime(0);
		$url = array();
		
		if ($author) {
			$so->setAuthor($author);
			$url['author'] = $author;
		}
		if ($fid) {
			$so->setFid($fid);
			$url['fid'] = $fid;
		}
		if ($createdTimeStart) {
			$so->setCreateTimeStart(Pw::str2time($createdTimeStart));
			$url['created_time_start'] = $createdTimeStart;
		}
		if ($createdTimeEnd) {
			$so->setCreateTimeEnd(Pw::str2time($createdTimeEnd));
			$url['created_time_end'] = $createdTimeEnd;
		}

		$count = Wekit::load('forum.PwThread')->countSearchPost($so);
		$postdb = Wekit::load('forum.PwThread')->searchPost($so, $limit, $start);

		$this->setOutput($postdb, 'postdb');
		$this->setOutput(Wekit::load('forum.srv.PwForumService')->getForumList(), 'forumlist');
		$this->setOutput(Wekit::load('forum.srv.PwForumService')->getForumOption(), 'option_html');

		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($count, 'count');
		$this->setOutput($url, 'url');
	}

	public function doPassPostAction() {

		$pid = $this->getInput('pid');
		if (empty($pid)) {
			$this->showError('operate.select');
		}
		!is_array($pid) && $pid = array($pid);
		
		Wind::import('SRV:forum.srv.dataSource.PwFetchReplyByPid');
		Wind::import('SRV:forum.srv.operation.PwPassReply');

		$service = new PwPassReply(new PwFetchReplyByPid($pid));
		$service->execute();

		$this->showMessage('success');
	}

	public function doDeletePostAction() {

		$pid = $this->getInput('pid');
		if (empty($pid)) {
			$this->showError('operate.select');
		}
		!is_array($pid) && $pid = array($pid);

		Wind::import('SRV:forum.srv.operation.PwDeleteReply');
		Wind::import('SRV:forum.srv.dataSource.PwFetchReplyByPid');
		$deleteReply = new PwDeleteReply(new PwFetchReplyByPid($pid), PwUserBo::getInstance($this->adminUser->getUid()));
		$deleteReply->setIsDeductCredit(1)->execute();

		$this->showMessage('success');
	}
}
?>