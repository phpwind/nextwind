<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidClientBo.php 22962 2013-01-04 05:11:30Z gao.wanggao $ 
 * @package 
 */
class WindidClientBo {
	public $windid = 'local';
	public $serverUrl = '';
	public $serverIp = '';
	public $clientKey = '';
	public $clientId = 0;
	public $clientDb = 'mysql';
	public $clientCharser = 'utf8';
	public $timevc = 0;
	private static $_clientBo = null;
	
	public function __construct($config = array()) {
		$config =  $config ? $config : include Wind::getRealPath('WINDID:conf.config.php', true);
		$this->windid = $config['windid'];
		$this->serverUrl = $config['serverUrl'];
		$this->serverIp = isset($config['serverIp']) ? $config['serverIp'] : '';
		$this->clientKey = $config['clientKey'];
		$this->clientId = $config['clientId'];
		$this->clientDb = $config['clientDb'];
		$this->clientCharser = $config['clientCharser'];
		$this->timevc = isset($config['timevc']) ? $config['timevc'] : 0;
	}
	
	public static function getInstance($config = array()) {
		if (!isset(self::$_clientBo)) self::$_clientBo = new self($config);
		return self::$_clientBo;
	}
	
	
}
?>