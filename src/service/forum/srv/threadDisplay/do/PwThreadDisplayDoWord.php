<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase');

/**
 * 投票展示
 *
 * @author MingXing Sun <mingxing.sun@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwThreadDisplayDoPoll.php 19692 2012-10-17 05:16:40Z jieyin $
 * @package poll
 */

class PwThreadDisplayDoWord extends PwThreadDisplayDoBase {
	
	public function bulidRead($read) { 
		$wordFilter = Wekit::load('SRV:word.srv.PwWordFilter');
		$content = $read['subject']. '<wind>' .$read['content'];
		$content = $wordFilter->replaceWord($content, $read['word_version']);
		if ($content === false) return $read;
		list($read['subject'], $read['content']) = explode('<wind>', $content);
		return $read;
	}
}