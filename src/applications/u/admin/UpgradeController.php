<?php
Wind::import('ADMIN:library.AdminBaseController');

/**
 * 后台用户组提升方案
 * 
 * @author peihong.zhangph <peihong.zhangph@aliyun-inc.com> Nov 21, 2011
 * @link http://www.phpwind.com
 * @copyright 2011 phpwind.com
 * @license
 * @version $Id: UpgradeController.php 20860 2012-11-14 02:53:05Z xiaoxia.xuxx $
 */

class UpgradeController extends AdminBaseController {
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		/* @var $configService PwConfig */
		$configService = Wekit::load('config.PwConfig');
		$config = $configService->getValues('site');
		$strategy = $config['upgradestrategy'];
		
		Wind::import('SRV:credit.bo.PwCreditBo');
		/* @var $pwCreditBo PwCreditBo */
		$pwCreditBo = PwCreditBo::getInstance();
		$this->setOutput($pwCreditBo, 'credits');
		$this->setOutput($strategy, 'member');
	}
	
	/**
	 * 配置增加表单处理器
	 *
	 * @return void
	 */
	public function dosaveAction() {

		$member = $this->getInput('member', 'post');

		$strategy = array();
		Wind::import('SRV:credit.bo.PwCreditBo');
		/* @var $pwCreditBo PwCreditBo */
		$pwCreditBo = PwCreditBo::getInstance();
	
		foreach ($pwCreditBo->cType as $k => $v) {
			$vkey = 'credit' . $k;
			$member[$vkey] && $strategy[$vkey] = $member[$vkey];
		}
		foreach (array('postnum','onlinetime', 'digest') as $v) {
			$member[$v] && $strategy[$v] = $member[$v];
		}

		$config = new PwConfigSet('site');
		$config->set('upgradestrategy' , $strategy)->flush();

		$this->showMessage('success', 'u/upgrade/run/', true);
	}
}