<?php

/**
 * 粉丝controller
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: FansController.php 18754 2012-09-27 04:06:31Z xiaoxia.xuxx $
 * @package forum
 */

class FansController extends PwBaseController {
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		if (!$this->loginUser->isExists()) {
			$this->forwardAction('u/login/run',array('backurl' => WindUrlHelper::createUrl('my/fans/run')));
		}
		$this->setOutput('fans', 'li');
    }

	public function run() {
		
		$page = intval($this->getInput('page'));
		$page < 1 && $page = 1;
		$perpage = 20;
		list($start, $limit) = Pw::page2limit($page, $perpage);
		
		$count = $this->loginUser->info['fans'];
		$fans = $this->_getDs()->getFans($this->loginUser->uid, $limit, $start);
		$uids = array_keys($fans);
		$follows = $this->_getDs()->fetchFollows($this->loginUser->uid, $uids);
		$userList = Wekit::load('user.PwUser')->fetchUserByUid($uids, PwUser::FETCH_MAIN | PwUser::FETCH_DATA | PwUser::FETCH_INFO);
		$this->setOutput(WindUtility::mergeArray($fans, $userList), 'fans');
		$this->setOutput($follows, 'follows');

		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($count, 'count');
		//$this->setOutput($url, 'url');
		
		// seo设置
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		PwSeoBo::setCustomSeo($lang->getMessage('SEO:bbs.fans.run.title'), '', '');
	}
	
	/**
	 * PwAttention
	 * 
	 * @return PwAttention
	 */
	private function _getDs() {
		return Wekit::load('attention.PwAttention');
	}
	
	/**
	 * PwAttentionRecommendFriendsService
	 *
	 * @return PwAttentionRecommendFriendsService
	 */
	protected function _getRecommendService() {
		return Wekit::load('attention.srv.PwAttentionRecommendFriendsService');
	}
}