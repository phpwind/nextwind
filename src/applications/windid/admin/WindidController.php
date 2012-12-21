<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidController.php 22343 2012-12-21 09:59:29Z gao.wanggao $ 
 * @package 
 */
class WindidController extends AdminBaseController {
	
	public function run() {
		$config = $this->_getConfigDs()->getValues('site');
		/*$client = Windid::client();
		
		$mysql = include Wind::getRealPath('WINDID:conf.database.php', true);
		if (!isset($mysql['dsn'])) $mysql = include Wind::getRealPath('CONF:database.php', true);
	
		$dsn = explode(';', $mysql['dsn']);
		list($key, $mysql['host']) = explode('=', $dsn[0]);
		list($key, $mysql['dbname']) = explode('=', $dsn[1]);
		list($key, $mysql['port']) = explode('=', $dsn[2]);
		if ($mysql['port']) $mysql['host'] = $mysql['host'] . ':' . $mysql['port'];
		$this->setOutput($client, 'client');
		$this->setOutput($mysql, 'mysql');*/
		$this->setOutput($config, 'config');
	}
	
	public function dorunAction() {
		$windid = $this->getInput('windid', 'post');
		/*$array = array(
			'serverUrl', 'clientId','clientKey','clientDb','clientCharser'
		);
		$client = $this->getInput($array, 'post');
		$client = array_combine($array, $client);
		
		if ($windid == 'client' && (!$client['serverUrl'] || !$client['clientId'])) $this->showError('ADMIN:fail');
		
		$array = array(
			'dbhost', 'dbuser','dbpwd','dbname','dbprefix','engine'
		);
		$mysql = $this->getInput($array, 'post');
		$mysql = array_combine($array, $mysql);
		list($mysql['dbhost'], $mysql['dbport']) = explode(':', $mysql['dbhost']);
		$mysql['dbport'] = !empty($mysql['dbport']) ? intval($mysql['dbport']) : 3306;
		if (!empty($mysql['engine'])) {
			$mysql['engine'] = strtoupper($mysql['engine']);
			!in_array($mysql['engine'], array('MyISAM', 'InnoDB')) && $mysql['engine'] = 'MyISAM';
		} else {
			$mysql['engine'] = 'MyISAM';
		}*/
		$config = new PwConfigSet('site');
		$config->set('windid', $windid)->flush();
		//$this->_getWindid()->setConfig('windid', 'windid', $windid); 
		
		/*$charset = Wind::getApp()->getResponse()->getCharset();
		$charset = str_replace('-', '', strtolower($charset));
		if (!in_array($charset, array('gbk', 'utf8', 'big5'))) $charset = 'utf8';
		$clientCharser = $client['clientCharser'] ? $client['clientCharser'] : $charset;
		$configfile = Wind::getRealPath('CONF:windidConfig.php', true);
		if ($windid != 'client') {
			$baseUrl = Wind::getApp()->getRequest()->getBaseUrl(true);
			$client['clientDb'] = 'mysql';
			$client['serverUrl'] = $baseUrl;
			$client['clientId'] = 0;
			$client['clientKey'] = '';
		}
		$config = array(
			'windid'  => $windid,
			'serverUrl' => $client['serverUrl'],
			'clientId'  => $client['clientId'],
			'clientKey'  => $client['clientKey'],
			'clientDb'  => $client['clientDb'],
			'clientCharser'  => $clientCharser,
		);
		WindFile::savePhpData($configfile,$config);*/
		
		/*$datafile = Wind::getRealPath('WINDID:conf.database.php', true);
		$database = array(
			'dsn' => 'mysql:host=' . $mysql['dbhost'] . ';dbname=' . $mysql['dbname'] . ';port=' . $mysql['dbport'],
			'user' => $mysql['dbuser'],
			'pwd' => $mysqll['dbpw'],
			'charset' => $charset,
			'tableprefix' => $mysql['dbprefix'],
			//'engine' => $mysql['engine'],
		);
		WindFile::savePhpData($datafile,$database);*/
		$this->showMessage('ADMIN:success');
	}
	
	private function _getConfigDs() {
		return Wekit::load('config.PwConfig');
	}
	
	private function _getWindid() {
		return WindidApi::api('config');
	}
}
?>