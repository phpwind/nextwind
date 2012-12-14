<?php
/**
 * 系统默认的错误处理类
 * 系统默认错误处理类,当不配置任何错误处理句柄定义时,该类自动被用于错误处理.
 * 可以通过配置'error'模块,或者重定义'error-handler'来改变当前的错误处理句柄.<code>
 * <module name='default'>
 * <error-handler>WIND:core.web.WindErrorHandler</error-handler>
 * ...
 * </module>
 * </code>
 * 
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindErrorHandler.php 3829 2012-11-19 11:13:22Z yishuo $
 * @package web
 */
class WindErrorHandler extends WindController {
	protected $error = array();
	protected $errorCode = 0;
	protected $errorDir;
	
	/*
	 * (non-PHPdoc) @see WindAction::beforeAction()
	 */
	public function beforeAction($handlerAdapter) {
		$this->errorDir = $this->getInput('__errorDir', 'post');
		$error = $this->getInput('__error', 'post');
		$this->error = $error['message'];
		$this->errorCode = $error['code'];
	}
	
	/*
	 * (non-PHPdoc) @see WindAction::run()
	 */
	public function run() {
		$this->setOutput("Error message", "errorHeader");
		$this->setOutput($this->error, "errors");
		$this->setTemplatePath($this->errorDir);
		$this->setTemplate('erroraction');
	}
}