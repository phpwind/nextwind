<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:ubb.PwSimpleUbbCode');
Wind::import('LIB:ubb.config.PwUbbCodeConvertThread');

/**
 * 帖子公共服务
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: PwThreadService.php 20735 2012-11-06 05:42:19Z yanchixia $
 * @package forum
 */

class PwThreadService {
	
	public function displayReplylist($replies, $contentLength = 140) {
		foreach ($replies as $key => $value) {
			$value['content'] = WindSecurity::escapeHTML($value['content']);
			if (!empty($value['ifshield'])) {
				$value['content'] = '<div class="shield">此帖已被屏蔽</div>';
			} elseif ($value['useubb']) {
				$ubb = new PwUbbCodeConvertThread();
				$value['reminds'] && $ubb->setRemindUser($value['reminds']);
				$value['content'] = PwSimpleUbbCode::convert($value['content'], $contentLength, $ubb);
			} else {
				$value['content'] = Pw::substrs($value['content'], $contentLength);
			}
			$replies[$key] = $value;
		}
		return $replies;
	}

	public function displayContent($content, $useubb, $remindUser = array(), $contentLength = 140) {
		$content = WindSecurity::escapeHTML($content);
		if ($useubb) {
			$ubb = new PwUbbCodeConvertThread();
			$ubb->setRemindUser($remindUser);
			$content = PwSimpleUbbCode::convert($content, $contentLength, $ubb);
		} else {
			$content = Pw::substrs($content, $contentLength);
		}
		return $content;
	}
}