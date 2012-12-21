<?php
Wind::import('SRV:forum.srv.post.do.PwPostDoBase');
Wind::import('SRV:word.PwWord');

/**
 * 发帖子检测敏感词
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class PwPostDoWord extends PwPostDoBase {
	protected $_isVerified = 0;
	protected $_confirm = 0;
	protected $_word = 0;
	protected $_tagnames = array();
	
	public function __construct(PwPost $pwpost, $verifiedWord = 0, $tagnames = array()) {
		$this->_confirm = $verifiedWord;
		$this->_tagnames = $tagnames;
	}

	public function check($postDm) {
		$data = $postDm->getData();
		$content = $data['subject'].$data['content'];
		$wordFilter = Wekit::load('SRV:word.srv.PwWordFilter');
		if ($this->_tagnames) {
			$tagname = implode(' ', $this->_tagnames);
			if (($result = $wordFilter->filter($tagname)) === true) {
				return new PwError('WORD:tag.content.error');
			}
		}
		list($type, $words) = $wordFilter->filterWord($content);
		$words = $words ? $words : '';
		if (!$type) return true; 
		$this->_word = 1;
		switch ($type) {
			case 1:
				return new PwError('WORD:content.error',array(),array('word' => $words));
			case 2:
				$this->_isVerified = 1;
				if ($this->_confirm) {
						return true;
				}
				return new PwError('WORD:content.error',array(),array('isVerified' => $this->_isVerified, 'word' => $words));
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
