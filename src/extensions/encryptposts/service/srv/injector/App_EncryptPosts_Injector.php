<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRC:library.engine.hook.PwBaseHookInjector');
Wind::import('EXT:encryptposts.service.srv.App_EncryptPosts_EncryptPostsService');
Wind::import('EXT:encryptposts.service.srv.App_EncryptPosts_ReadService');


/**
 * 帖子加密 注入服务
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */

class App_EncryptPosts_Injector extends PwBaseHookInjector {

	/**
 	 *
	 * 注入器--发帖页展示
	 */
	public function run() {
		return new App_EncryptPosts_EncryptPostsService($this->bp);
	}

	/**
	 *
	 *注入器--帖子提交
	 */
	public function doadd(){		
		$input = $this->getInput('encryptposts', 'post');
		$encryptPosts = new App_EncryptPosts_EncryptPostsService($this->bp);
		$encryptPosts->setEncryptPostsInput($input);
		return $encryptPosts;
	}

	/**
	 *注入器 - 帖子编辑页
	 */
	public function updateHtml(){
		$tid = $this->getInput('tid');
		return new App_EncryptPosts_EncryptPostsService($this->bp,$tid);		
		return $service;
	}
	
	/**
	 *注入器 - 发布编辑的帖子
	 */
	public function domodify(){
		$input = $this->getInput('encryptposts', 'post');
		$encryptPosts = new App_EncryptPosts_EncryptPostsService($this->bp);
		$encryptPosts->setEncryptPostsInput($input);
		return $encryptPosts;
	}

	/**
	 *注入器 - 帖子阅读页
	 */
	public function read(){
		$token = $this->getInput('token', 'get');
		$server = new App_EncryptPosts_ReadService($this->bp);
		if($token) $server->setToken($token);
		return $server;
	}




}