<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.srv.post.do.PwPostDoBase');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: xiaoxia.xuxx $>
 * @author $Author: xiaoxia.xuxx $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwLikeDoFresh.php 20459 2012-10-30 06:12:01Z xiaoxia.xuxx $ 
 * @package 
 */

class PwLikeDoFresh extends PwPostDoBase {
	private $info = array();
	private $content = '';
	private $userBo;
	
	public function __construct(PwPost $pwpost, $content = '') {
		$this->info = $pwpost->action->getInfo();
		$this->content = $this->info['subject'] ? $this->info['subject'] : $content;
		$this->userBo = $pwpost->user;
	}
	
	public function addPost($pid, $tid) {
		$url = isset($this->info['pid']) ?  '&pid=' . $this->info['pid'] : '';
		$url = WindUrlHelper::createUrl('bbs/read/run/?tid='. $this->info['tid'] . '&fid=' . $this->info['fid'] . $url);
		$lang = Wind::getComponent('i18n');
		$content = $lang->getMessage("BBS:like.like.flesh") . '[url=' .$url .']' .$this->content . '[/url]';
		Wind::import('SRV:weibo.dm.PwWeiboDm');
      	Wind::import('SRV:weibo.srv.PwSendWeibo');
      	Wind::import('SRV:weibo.PwWeibo');
      	$dm = new PwWeiboDm();
      	$dm->setContent($content)
      		->setType(PwWeibo::TYPE_LIKE);
      	$sendweibo = new PwSendWeibo($this->userBo);
      	$sendweibo->send($dm);
      	return true;
	}
}