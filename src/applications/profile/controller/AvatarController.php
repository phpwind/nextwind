<?php
Wind::import('APPS:.profile.controller.BaseProfileController');
Wind::import('SRV:user.PwUserBan');

/**
 * 用户头像处理
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: AvatarController.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package src.products.u.controller.profile
 */
class AvatarController extends BaseProfileController {
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$this->setCurrentLeft('avatar');
		$isAvatarBan = false;
		if (Pw::getstatus($this->loginUser->info['status'], PwUser::STATUS_BAN_AVATAR)) {
			Wind::import('SRV:user.srv.PwBanBp');
			$banBp = new PwBanBp($this->loginUser->uid);
			if (false === $banBp->checkIfBanAvatar()) {
				$banBp->recoveryBanAvatarError();
			} elseif ($banBp->endDateTimeBanAvatar()) {
				$this->loginUser->info['status'] = $banBp->callEndDateTimeBanAvatar();
			} else {
				$isAvatarBan = true;
				$info = $banBp->getBanAvatarInfo();
				if ($info['created_userid'] == 0) {
					$info['operator'] = 'system';
				} else {
					$operatorInfo = Wekit::load('user.PwUser')->getUserByUid($info['created_userid']);
					$info['operator'] = $operatorInfo['username'];
				}
				$this->setOutput($info, 'banInfo');
			}
		}
		$windidApi = $this->_getWindid();
		$this->setOutput($windidApi->showFlash($this->loginUser->uid), 'avatarFlash');
		$this->setOutput($windidApi->showFlash($this->loginUser->uid, 0), 'avatarArr');
		$this->setOutput($isAvatarBan, 'isAvatarBan');
		$this->setOutput($this->getInput('type'), 'type');
		$this->setLayout('');
		$this->setTemplate('profile_avatar');
		
		// seo设置
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		PwSeoBo::setCustomSeo($lang->getMessage('SEO:profile.avatar.run.title'), '', '');
	}
	
	private function _getWindid() {
		return WindidApi::api('avatar');
}
}
