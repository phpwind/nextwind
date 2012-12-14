<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('WIND:mail.WindMail');

/**
 * 发邮件组件
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwMail.php 18618 2012-09-24 09:31:00Z jieyin $
 * @package Lib:utility.PwMail
 */
class PwMail {
	
	private $_config = '';
	/**
	 * @var WindMail
	 */
	private $_mail;
	
	public function __construct() {
		$config = Wekit::C('email');
		$this->_config = array(
			'mailOpen' => $config['mailOpen'], 
			'mailMethod' => $config['mailMethod'], 
			'host' => $config['mail.host'], 
			'port' => $config['mail.port'], 
			'from' => $config['mail.from'], 
			'auth' => $config['mail.auth'], 
			'user' => $config['mail.user'], 
			'password' => $config['mail.password']);
		$this->_mail = new WindMail();
		$this->_mail->setCharset(Wekit::app()->charset);
		$this->_mail->setDate(date('r', Pw::getTime()));
		$this->_mail->setContentEncode(WindMail::ENCODE_BASE64);
		$this->_mail->setContentType(WindMail::MIME_HTML);
		$this->_mail->setFrom($this->_config['from'], Wekit::C('site', 'info.name'));
	}

	/**
	 * 普通发邮件方法
	 *
	 * @param string $toUser 收件人
	 * @param string $subject 邮件标题
	 * @param string $content 邮件内容
	 * @return bool
	 */
	public function sendMail($toUser, $subject, $content) {
		if (!$this->_config['mailOpen']) return false;
		$this->_mail->setSubject($subject);
		$this->_mail->setTo($toUser);
		$this->_mail->setBody($content);
		try {
			$this->_mail->send($this->getMethod(), $this->_config);
		}catch(WindException $e) {
			//TODO 邮件发送失败
			return new PwError($e->getMessage());
		}
		return true;
	}

	/**
	 * 根据后台配置获取发邮件方式
	 *
	 * return string
	 */
	private function getMethod() {
		$methodMap = array(1 => 'php', 2 => 'smtp', 3 => 'send');
		return isset($methodMap[$this->_config['mailMethod']]) ? $methodMap[$this->_config['mailMethod']] : 'smtp';
	}
}