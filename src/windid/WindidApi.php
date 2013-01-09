<?php
!defined('WINDID') && define('WINDID', dirname(__FILE__));
!defined('WINDID_PATH') && define('WINDID_PATH', WINDID);
!defined('WINDID_VERSION') && define('WINDID_VERSION', '0.0.2');
if (!defined('WEKIT_VERSION')) {
	require_once (WINDID.'/../../wind/Wind.php');
	$database =  include WINDID.'/conf/database.php';
	Wind::register(WINDID, 'WINDID');
	Wind::application();
	Wind::import('WINDID:library.Windid');
	$component = array('path' => 'WIND:db.WindConnection', 'config'=>$database);
	Wind::registeComponent($component, 'windiddb', 'singleton');
}
class WindidApi {
	
	public static function api($api) {
		static $cls = array();
		$array = array('user', 'config', 'message', 'avatar', 'area', 'school');
		if (!in_array($api, $array)) return WindidError::FAIL;
		$class = 'Windid'.ucfirst($api).'Api';
		if (!isset($cls[$class])) {
			if (Windid::client()->clientDb == 'mysql') {
				$class = Wind::import('WINDID:api.local.'.$class);
				$cls[$class] = new $class();
			} elseif (Windid::client()->clientDb == 'http') {
				$class = Wind::import('WINDID:api.web.'.$class);
				$cls[$class] = new $class();
			} else {
				return WindidError::FAIL;
			}
		}
		return $cls[$class];
	}
	
	public static function open($script, $getData = array(), $postData = array(), $method='post', $protocol='http') {
		$client = Windid::client();
		$time = time() + $client->timecv * 60;
		list($c, $a) = explode('/', $script);
		$query = array(
			'm'=>'api',
			'c'=>$c,
			'a'=>$a,
			'windidkey'=>WindidUtility::appKey($client->clientId, $time, $client->clientKey),
			'clientid'=>$client->clientId,
			'time'=>$time,
		);
		$url = $client->serverUrl  . '/windid/index.php?' . http_build_query($query) .'&' . http_build_query($getData);
		$result = WindidUtility::buildRequest($url, $postData);
		if ($result === false) return WindidError::SERVER_ERROR;
		return WindJson::decode($result, true, $client->clientCharser);
	}
	
	public static function getDm($api) {
		$array = array('user', 'message', 'credit');
		if (!in_array($api, $array)) return WindidError::FAIL;
		switch ($api) {
			case 'user':
				return Wind::import('WINDID:service.user.dm.WindidUserDm');
			case 'message':
				return Wind::import('WINDID:service.message.dm.WindidMessageDm');
			case 'credit':
				return Wind::import('WINDID:service.user.dm.WindidCreditDm');
		}
				
	}
	
}
