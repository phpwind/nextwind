<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('APPS:admin.library.AdminBaseController');
Wind::import('LIB:engine.error.PwError');
/**
 * 回复奖励 后台设置 
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class ManageController extends AdminBaseController {

	public function run(){
		list($groupType) = $this->getInput(array('type'), 'get');
		$groupType or $groupType = 'member';
		$groups = $this->_getGroupDs()->getGroupsByTypeInUpgradeOrder($groupType);
		$groupTypes = $this->_getGroupDs()->getGroupTypes();

		$groupInfo = $this->_getGroupDs()->getGroupsByType($groupType);
		$gids = array();
		foreach($groupInfo as $v){
			$gids[] = intval($v['gid']);
		}
		$configByGids = $this->_getReplyRewardConfigService()->getReplyRewardConfigByGids($gids);
		$configInfo = array();
		foreach($configByGids as $key => $value){
			$configInfo[$key] = $value;
		}

		$typeClasses = array();
		foreach ($groupTypes as $v){
			$typeClasses[$v] = $groupType == $v ? ' class="current"' : '';//TODO
		}
		$this->setOutput($typeClasses, 'typeClasses');
		$this->setOutput($configInfo, 'configInfo');
		$this->setOutput($groupType, 'groupType');
		$this->setOutput($groups, 'groups');
	}

	public function doSetAction(){
		$config = $this->getInput('conf', 'post');
		//var_dump($config);exit;
		if(!is_array($config)) 
			$this->showError('非法操作');
		$config = array_map('intval', $config);

		$this->_getReplyRewardConfigService()->setReplyRewardAdminConfig($config);

		$this->showMessage('设置成功');
	}

	private function _getGroupDs(){
		return Wekit::load('usergroup.PwUserGroups');
	}

	private function _getReplyRewardConfigService(){
		return Wekit::load('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardConfigService');
	}

}