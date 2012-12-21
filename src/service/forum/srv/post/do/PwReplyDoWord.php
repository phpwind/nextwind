<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.srv.post.do.PwPostDoBase');
/**
 * 帖子回复 - 敏感词
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class PwReplyDoWord extends PwPostDoBase {
	protected $_isVerified = 0;
	protected $_confirm = 0;
	protected $_word = 0;
	
	public function __construct(PwPost $pwpost, $verifiedWord = 0) {
		$this->_confirm = $verifiedWord;
	}

	public function check($postDm) {
		$data = $postDm->getData();
		$content = $data['subject'].$data['content'];
		$wordFilter = Wekit::load('SRV:word.srv.PwWordFilter');

		list($type, $words) = $wordFilter->filterWord($content);
		$words = $words ? $words : array();
		if (!$type) return true; 
		$this->_word = 1;
		switch ($type) {
			case 1:
				return new PwError('WORD:content.error.tip',array('{wordstr}' => implode(',', $words)));
			case 2:
				$this->_isVerified = 1;
				if ($this->_confirm) {
						return true;
				}
			case 3:	
			default:
				return true;
		}
		return true;
	}
	
	public function dataProcessing($postDm) {
		$word_version = $this->_word ? 0 : (int)Wekit::C('bbs', 'word_version');
		$this->_isVerified && $postDm->setDisabled(1);
		$postDm->setWordVersion($word_version);
		return $postDm;
	}
}