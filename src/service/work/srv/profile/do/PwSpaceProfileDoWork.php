<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:space.srv.profile.do.PwSpaceProfileDoInterface');

/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwSpaceProfileDoWork.php 6508 2012-03-21 08:02:57Z gao.wanggao $ 
 * @package 
 */
class PwSpaceProfileDoWork implements PwSpaceProfileDoInterface {
	
	public function createHtml($spaceBo) {
		if (!$spaceBo->allowView('work')) return false;
		$spaceBo->spaceUser['work'] = Wekit::load('work.PwWork')->getByUid($spaceBo->spaceUid);
		PwHook::template('work', 'TPL:space.profile_extend', true, $spaceBo);
	}
}