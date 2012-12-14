<?php

/**
 * 访问脚印
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: VisitorController.php 19677 2012-10-17 03:36:26Z jinlong.panjl $
 * @package wind
 */
class VisitorController extends PwBaseController {
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		if (!$this->loginUser->isExists()) {
			$this->forwardAction('u/login/run',array('backurl' => WindUrlHelper::createUrl('my/visitor/run')));
		}
		$this->setOutput('visitor', 'li');
    }
	
	/**
	 * 谁看过我
	 */
	public function run() {
		$space = $this->_getSpaceDs()->getSpace($this->loginUser->uid);
		$visitors = $space['visitors'] ? unserialize($space['visitors']) : array();
		$uids = array_keys($visitors);
		if ($uids) {
			$userList = Wekit::load('user.PwUser')->fetchUserByUid($uids, PwUser::FETCH_MAIN | PwUser::FETCH_DATA | PwUser::FETCH_INFO);
			$userList = $this->_buildData($userList, $uids);
			$follows = $this->_getAttentionDs()->fetchFollows($this->loginUser->uid, $uids);
			$fans = $this->_getAttentionDs()->fetchFans($this->loginUser->uid, $uids);
			$friends = array_intersect_key($fans, $follows);
			$this->setOutput($fans, 'fans');
			$this->setOutput($friends, 'friends');
			$this->setOutput($userList, 'userList');
			$this->setOutput($follows, 'follows');
		} else {
			Wind::import('SRV:user.vo.PwUserSo');
			$vo = new PwUserSo();
			$vo->orderbyLastpost(false);
			$lastPostUser = Wekit::load('SRV:user.PwUserSearch')->searchUser($vo, 2);
			if ($lastPostUser) {
				unset($lastPostUser[$this->loginUser->uid]);
				$lastPostUser = array_keys($lastPostUser);
				$this->setOutput($lastPostUser[0], 'lastPostUser');
			}
		}
		$this->setOutput($visitors, 'visitors');
		
		// seo设置
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		PwSeoBo::setCustomSeo($lang->getMessage('SEO:bbs.visitor.run.title'), '', '');
	}
	
	/**
	 * 我看过谁
	 */
	public function tovisitAction() {
		$space = $this->_getSpaceDs()->getSpace($this->loginUser->uid);
		$visitors = $space['tovisitors'] ? unserialize($space['tovisitors']) : array();
		$uids = array_keys($visitors);
		if ($uids) {
			$userList = Wekit::load('user.PwUser')->fetchUserByUid($uids, PwUser::FETCH_MAIN | PwUser::FETCH_DATA | PwUser::FETCH_INFO);
			$userList = $this->_buildData($userList, $uids);
			$follows = $this->_getAttentionDs()->fetchFollows($this->loginUser->uid, $uids);
			$fans = $this->_getAttentionDs()->fetchFans($this->loginUser->uid, $uids);
			$friends = array_intersect_key($fans, $follows);
			$this->setOutput($friends, 'friends');
			$this->setOutput($userList, 'userList');
			$this->setOutput($follows, 'follows');
			$this->setOutput($fans, 'fans');
		} else {
			Wind::import('SRV:user.vo.PwUserSo');
			$vo = new PwUserSo();
			$vo->orderbyLastpost(false);
			$lastPostUser = Wekit::load('SRV:user.PwUserSearch')->searchUser($vo, 2);
			if ($lastPostUser) {
				unset($lastPostUser[$this->loginUser->uid]);
				$lastPostUser = array_keys($lastPostUser);
				$this->setOutput($lastPostUser[0], 'lastPostUser');
			}
		}
		$this->setOutput($visitors, 'visitors');
	}
	
	private function _buildData($data,$keys) {
		$temp = array();
		foreach ($keys as $v) {
			$temp[$v] = $data[$v];
		}
		return $temp;
	}
	
	/**
	 * PwAttention
	 * 
	 * @return PwAttention
	 */
	private function _getAttentionDs() {
		return Wekit::load('attention.PwAttention');
	}
	
 	/**
 	 * PwSpace
 	 *
 	 * @return PwSpace
 	 */
 	private function _getSpaceDs() {
 		return Wekit::load('space.PwSpace');
 	}
}