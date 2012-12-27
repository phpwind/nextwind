<?php
Wind::import('SRV:forum.srv.PwThreadList');

/**
 * 默认站点首页
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: IndexController.php 22775 2012-12-27 06:57:28Z jieyin $
 * @package forum
 */

class IndexController extends PwBaseController {

	public function run() {

		$order = $this->getInput('order', 'get');
		$page = intval($this->getInput('page', 'get'));
		
		$threadList = new PwThreadList();
		$this->runHook('c_index_run', $threadList);

		$threadList->setPage($page)->setPerpage(Wekit::C('bbs', 'thread.perpage'));
		
		Wind::import('SRV:forum.srv.threadList.PwNewThread');
		$forbidFids = Wekit::load('forum.srv.PwForumService')->getForbidVisitForum($this->loginUser);
		$dataSource = new PwNewThread($forbidFids);
		if ($order == 'postdate') {
			$dataSource->setOrderBy($order);
		} else {
			$dataSource->setOrderBy('lastpost');
		}
		$threadList->execute($dataSource);
		if ($threadList->total > 12000) {
			Wekit::load('forum.PwThreadIndex')->deleteOver($threadList->total - 10000);
		}
		$threaddb = $threadList->getList();
		$fids = array();
		foreach ($threaddb as $key => $value) {
			$fids[] = $value['fid'];
		}
		$forums = Wekit::load('forum.srv.PwForumService')->fetchForum($fids);
		
		if ($operateThread = $this->loginUser->getPermission('operate_thread', false, array())) {
			$operateThread = Pw::subArray($operateThread, array('delete'));
		}
		
		$this->setOutput($threadList, 'threadList');
		$this->setOutput($threaddb, 'threaddb');
		$this->setOutput($forums, 'forums');
		$this->setOutput($threadList->icon, 'icon');
		$this->setOutput($threadList->uploadIcon, 'uploadIcon');
		$this->setOutput(26, 'numofthreadtitle');
		$this->setOutput($order, 'order');
		$this->setOutput($operateThread, 'operateThread');
		
		$this->setOutput($threadList->page, 'page');
		$this->setOutput($threadList->perpage, 'perpage');
		$this->setOutput($threadList->total, 'count');
		$this->setOutput($threadList->maxPage, 'totalpage');
		$this->setOutput($threadList->getUrlArgs(), 'urlargs');
		
		// seo设置
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		$threadList->page <=1 && PwSeoBo::setDefaultSeo($lang->getMessage('SEO:bbs.forum.run.title'), '', $lang->getMessage('SEO:bbs.forum.run.description'));
		PwSeoBo::init('bbs', 'new');
		PwSeoBo::set('{page}', $threadList->page);
	}
}