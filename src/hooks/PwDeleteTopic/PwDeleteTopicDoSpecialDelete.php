<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:process.iPwGleanDoHookProcess');

/**
 * 帖子删除扩展服务接口--虚拟删除到回收站
 *
 * @author zhangpeihong@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDeleteTopicDoSpecialDelete.php 5735 2012-03-09 05:06:39Z xiaoxia.xuxx $
 * @package forum
 */

class PwDeleteTopicDoSpecialDelete extends iPwGleanDoHookProcess {
	
	protected $record = array();

	public function run($tids) {
		Wekit::load('forum.PwSpecialSort')->deleteSpecialSortByTids($tids);
	}
}