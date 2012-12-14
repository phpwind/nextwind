<?php
/**
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-13
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.net
 * @version $Id: PwException.php 4129 2012-02-11 05:03:58Z yishuo $
 * @package wekit
 * @subpackage exception
 */
class PwException extends WindException {

	/**
	 * @param string $message
	 * @param array $vars
	 * @param int $code
	 */
	public function __construct($message, $vars = array(), $code = 0) {
		$message = $this->buildMessage($message, $vars);
		$this->message = $message;
		$this->code = $code;
	}

	/**
	 * 根据exception code返回构建的异常信息描述
	 * 
	 * @param string $message 用户自定义的信息
	 * @param array $vars 异常信息中的变量值
	 * @return string 组装后的异常信息
	 */
	public function buildMessage($message, $vars) {
		if (strpos($message, 'EXCEPTION:') !== 0) {
			$message = 'EXCEPTION:' . $message;
		}
		return array($message, $vars);
	}
}

?>