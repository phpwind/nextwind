<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:process.iPwGleanDoHookProcess');

/**
 * 帖子删除扩展服务接口--虚拟删除到回收站
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDeleteReplyDoAttachDelete.php 7282 2012-03-31 13:22:24Z jieyin $
 * @package forum
 */

class PwDeleteReplyDoAttachDelete extends iPwGleanDoHookProcess {

	public function run($ids) {
		//$service = Wekit::load('forum.PwThread');
		//$service->batchDeleteThread($tids);
		//$service->batchDeletePostByTid($tids);
	}
}