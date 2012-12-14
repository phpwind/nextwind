<?php
/**
 * 命令行前端控制器
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindCommandFrontController.php 3791 2012-10-30 04:01:29Z liusanbian $
 * @package command
 */
class WindCommandFrontController extends AbstractWindFrontController {
	
	/*
	 * (non-PHPdoc) @see AbstractWindFrontController::run()
	 */
	public function run() {
		parent::run();
		exit("Completely Done~\r\n");
	}

	/**
	 * 创建并返回应用实例
	 * 
	 * @return WindWebApplication
	 */
	public function createApplication() {
		$this->_initConfig();
		$this->_appName || $this->_appName = 'default';
		/* @var $router WindRouter */
		$router = $this->getFactory()->getInstance('router');
		$router->route($this->getRequest());
		
		$this->_app = new WindCommandApplication($this->_request, $this->_factory, $router);
		if (!empty($this->_config['web-apps'][$this->_appName])) {
			if ($this->_appName !== 'default' && isset($this->_config['web-apps']['default'])) {
				$this->_config['web-apps'][$this->_appName] = WindUtility::mergeArray(
					$this->_config['web-apps']['default'], $this->_config['web-apps'][$this->_appName]);
				$this->_config['web-apps'][$this->_appName]['_merged'] = true;
			}
			$this->_app->setConfig($this->_config['web-apps'][$this->_appName]);
		}
	}

	/**
	 *
	 * @return WindHttpRequest
	 */
	public function getRequest() {
		if ($this->_request === null) {
			$this->_request = WindFactory::createInstance('WindCommandRequest');
		}
		return $this->_request;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFrontController::showErrorMessage()
	 */
	protected function showErrorMessage($message, $file, $line, $trace, $errorcode) {
		$log = $message . "\r\n" . $file . ":" . $line . "\r\n";
		list($fileLines, $trace) = WindUtility::crash($file, $line, $trace);
		foreach ($trace as $key => $value) {
			$log .= $value . "\r\n";
		}
		if (WIND_DEBUG & 2) Wind::getComponent('windLogger')->error($log, 'error', true);
		exit($log);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFrontController::_components()
	 */
	protected function _components() {
		return array(
			'router' => array('path' => 'WIND:command.WindCommandRouter', 'scope' => 'application'), 
			'windView' => array('path' => 'WIND:command.WindCommandView', 'scope' => 'prototype'), 
			'db' => array('path' => 'WIND:db.WindConnection', 'scope' => 'singleton'), 
			'configParser' => array('path' => 'WIND:parser.WindConfigParser', 'scope' => 'singleton'), 
			'errorMessage' => array('path' => 'WIND:base.WindErrorMessage', 'scope' => 'prototype'), 
			'windLogger' => array(
				'path' => 'WIND:log.WindLogger', 
				'scope' => 'singleton', 
				'destroy' => 'flush', 
				'constructor-args' => array('0' => array('value' => 'DATA:log'), '1' => array('value' => '2'))), 
			'i18n' => array(
				'path' => 'WIND:i18n.WindLangResource', 
				'scope' => 'singleton', 
				'config' => array('path' => 'i18n')));
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFrontController::_loadBaseLib()
	 */
	protected function _loadBaseLib() {
		Wind::$_imports += array(
			'WIND:i18n.WindLangResource' => 'WindLangResource', 
			'WIND:log.WindLogger' => 'WindLogger', 
			'WIND:base.WindErrorMessage' => 'WindErrorMessage', 
			'WIND:parser.WindConfigParser' => 'WindConfigParser', 
			'WIND:db.WindConnection' => 'WindConnection', 
			'WIND:command.WindCommandView' => 'WindCommandView', 
			'WIND:command.WindCommandRouter' => 'WindCommandRouter', 
			'WIND:command.WindCommandErrorHandler' => 'WindCommandErrorHandler', 
			'WIND:command.WindCmmandRequest' => 'WindCommandRequest', 
			'WIND:command.WindCommandResponse' => 'WindCommandResponse', 
			'WIND:command.WindCommandController' => 'WindCommandController', 
			'WIND:command.WindCommandApplication' => 'WindCommandApplication');
		
		Wind::$_classes += array(
			'WindLangResource' => 'i18n/WindLangResource', 
			'WindLogger' => 'log/WindLogger', 
			'WindErrorMessage' => 'base/WindErrorMessage', 
			'WindConfigParser' => 'parser/WindConfigParser', 
			'WindConnection' => 'db/WindConnection', 
			'WindCommandView' => 'command/WindCommandView', 
			'WindCommandRouter' => 'command/WindCommandRouter', 
			'WindCommandApplication' => 'command/WindCommandApplication', 
			'WindCommandController' => 'command/WindCommandController', 
			'WindCommandErrorHandler' => 'command/WindCommandErrorHandler', 
			'WindCommandRequest' => 'command/WindCommandRequest', 
			'WindCommandResponse' => 'command/WindCommandResponse');
	}
}
?>