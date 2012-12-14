<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 *
 * @author Zerol Lin <zerol.lin@me.com> Nov 25, 2012
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2103 phpwind.com
 * @license
 *
 *
 */
class IndexController extends PwBaseController {

	public function run() {
		$this->indexAction();
	}

	/**
	 * 无图版首页
	 */
	public function indexAction() {
		/* @var $forumService PwForumService */
		$forumService = Wekit::load("SRV:forum.srv.PwForumService");
		$forums = $forumService->getForumMap();
		$allowForums = $forumService->getAllowVisitForum($this->loginUser);
		foreach ($forums as $key => $lists) {
			foreach ($lists as $forum) {
				if (!$forum['isshow'] || !in_array($forum['fid'], $allowForums)) unset($forums[$key]);
			}
		}
		
		Wind::import('SRV:seo.bo.PwSeoBo');
		PwSeoBo::init('bbs', 'forumlist');

		$this->setOutput($forums, 'forums');
		$this->setTemplate('simple_index');
	}

	/**
	 * 无图版版块列表页
	 */
	public function threadAction() {
		list($fid, $page) = $this->getInput(array('fid', 'page'), 'GET');
		
		/* @var $pwforum PwForumBo */
		Wind::import('SRV:forum.bo.PwForumBo');
		$pwforum = new PwForumBo($fid, true);
		$this->_checkForumRight($pwforum);
		
		/* @var $threadList PwThreadList */
		Wind::import('SRV:forum.srv.PwThreadList');
		$threadList = new PwThreadList();
		$threadList->setPage($page)->setPerpage(100);
		
		/* @var $dataSource PwCommonThread */
		Wind::import('SRV:forum.srv.threadList.PwCommonThread');
		$dataSource = new PwCommonThread($pwforum);
		$threadList->execute($dataSource);
		
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		if ($threadList->page <= 1) {
			PwSeoBo::setDefaultSeo($lang->getMessage('SEO:bbs.thread.run.title'), '', 
				$lang->getMessage('SEO:bbs.thread.run.description'));
		}
		PwSeoBo::init('bbs', 'thread', $fid);
		PwSeoBo::set(
			array(
				'{forumname}' => $pwforum->foruminfo['name'], 
				'{forumdescription}' => Pw::substrs($pwforum->foruminfo['descrip'], 100, 0, false), 
				'{classification}' => '', 
				'{page}' => $threadList->page));
		
		$this->setOutput($threadList->getList(), 'threadList');
		$this->setOutput($fid, 'fid');
		$this->setOutput($pwforum, 'pwforum');
		
		$this->setOutput($threadList->page, 'page');
		$this->setOutput($threadList->perpage, 'perpage');
		$this->setOutput($threadList->total, 'count');
		$this->setOutput($threadList->maxPage, 'totalpage');
		
		$this->setTemplate('simple_thread');
	}

	/**
	 * 无图版帖子阅读页
	 */
	public function readAction() {
		list($tid, $page) = $this->getInput(array('tid', 'page'), 'GET');
		
		/* @var $threadDisplay PwThreadDisplay */
		Wind::import('SRV:forum.srv.PwThreadDisplay');
		$threadDisplay = new PwThreadDisplay($tid, $this->loginUser);
		
		if (($result = $threadDisplay->check()) !== true) {
			$this->showMessage($result->getError());
		}
		$pwforum = $threadDisplay->getForum();
		$this->_checkForumRight($pwforum);
		
		/* @var $dataSource PwCommonRead */
		Wind::import('SRV:forum.srv.threadDisplay.PwCommonRead');
		$dataSource = new PwCommonRead($threadDisplay->thread);
		$dataSource->setPage($page);
		$dataSource->setPerpage(25);
		$threadDisplay->execute($dataSource);
		
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		$threadDisplay->page <= 1 && PwSeoBo::setDefaultSeo($lang->getMessage('SEO:bbs.read.run.title'), '', 
			$lang->getMessage('SEO:bbs.read.run.description'));
		PwSeoBo::init('bbs', 'read');
		PwSeoBo::set(
			array(
				'{forumname}' => $threadDisplay->forum->foruminfo['name'], 
				'{title}' => $threadDisplay->thread->info['subject'], 
				'{description}' => Pw::substrs($threadDisplay->thread->info['content'], 100, 0, false), 
				'{classfication}' => $threadDisplay->thread->info['topic_type'], 
				'{tags}' => $threadDisplay->thread->info['tags'], 
				'{page}' => $threadDisplay->page));
		
		$this->setOutput($tid, 'tid');
		$this->setOutput($threadDisplay->fid, 'fid');
		$this->setOutput($threadDisplay->getThreadInfo(), 'threadInfo');
		$this->setOutput($threadDisplay->getList(), 'readdb');
		$this->setOutput($pwforum, 'pwforum');
		
		$this->setOutput($threadDisplay->page, 'page');
		$this->setOutput($threadDisplay->perpage, 'perpage');
		$this->setOutput($threadDisplay->total, 'count');
		$this->setOutput($threadDisplay->maxpage, 'totalpage');
		
		$this->setTemplate('simple_read');
	}

	private function _checkForumRight(PwForumBo $pwforum) {
		if (!$pwforum->isForum()) {
			$this->showError('BBS:forum.exists.not');
		}
		if ($pwforum->allowVisit($this->loginUser) !== true) {
			$this->showError(
				array(
					'BBS:forum.permissions.visit.allow', 
					array('{grouptitle}' => $this->loginUser->getGroupInfo('name'))));
		}
		if ($pwforum->forumset['jumpurl']) {
			$this->forwardRedirect($pwforum->forumset['jumpurl']);
		}
		if ($pwforum->foruminfo['password']) {
			if (!$this->loginUser->isExists()) {
				$this->forwardAction('u/login/run', 
					array('backurl' => WindUrlHelper::createUrl('bbs/cate/run?fid=' . $pwforum->fid)));
			} elseif (Pw::getPwdCode($pwforum->foruminfo['password']) != Pw::getCookie('fp_' . $pwforum->fid)) {
				$this->forwardAction('bbs/forum/password', array('fid' => $pwforum->fid));
			}
		}
	}
}