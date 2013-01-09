<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.bo.PwForumBo');

/**
 * 版块会员
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: UserController.php 23266 2013-01-07 08:46:40Z long.shi $
 * @package forum
 */

class UserController extends PwBaseController {

	public function run() {
		$fid = $this->getInput('fid');
		$type = intval($this->getInput('type', 'get')); // 主题分类ID
		$page = intval($this->getInput('page', 'get'));
		$page < 1 && $page = 1;
		$perpage = Wekit::C('bbs', 'thread.perpage');
		$pwforum = new PwForumBo($fid, true);
		
		if (!$pwforum->isForum(true)) {
			$this->showError('BBS:forum.exists.not');
		}
		if (($result = $pwforum->allowVisit($this->loginUser)) !== true) {
			$this->showError($result->getError());
		}
		
		$totalJoin = Wekit::load('forum.PwForumUser')->countUserByFid($fid);
		$joinUser = Wekit::load('forum.PwForumUser')->getUserByFid($fid, 15);
		$activeUser = Wekit::load('forum.srv.PwForumUserService')->getActiveUser($fid, 7, 50);
		$uids = array_merge(array_keys($joinUser), array_keys($activeUser));
		$users = Wekit::load('user.PwUser')->fetchUserByUid($uids);
		
		$guide = $pwforum->headguide();
		$guide .= $this->buildBread('会员', 'bbs/user/run?fid=' . $fid);
		$this->setOutput($fid, 'fid');
		$this->setOutput($pwforum, 'pwforum');
		$this->setOutput($guide, 'headguide');
		
		$this->setOutput($totalJoin, 'totalJoin');
		$this->setOutput($joinUser, 'joinUser');
		$this->setOutput($activeUser, 'activeUser');
		$this->setOutput($users, 'users');
		
		//版块风格
		//版块风格
		if ($pwforum->foruminfo['style']) {
			$this->setTheme('forum', $pwforum->foruminfo['style']);
			//$this->addCompileDir($pwforum->foruminfo['style']);
		}
		
		// seo设置
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		PwSeoBo::setCustomSeo(
			$lang->getMessage('SEO:bbs.user.run.title', array($pwforum->foruminfo['name'])), '', 
			$lang->getMessage('SEO:bbs.user.run.description', array($pwforum->foruminfo['name'])));
	}
}