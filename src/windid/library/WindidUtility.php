<?php
/**
 * Windid工具库
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidUtility.php 22349 2012-12-21 10:05:46Z gao.wanggao $
 * @package Windid.library
 */
class WindidUtility {

	/**
	 * 生成密码
	 *
	 * @param string $password 源密码
	 * @param string $salt
	 * @return string
	 */
	public static function buildPassword($password, $salt) {
		return md5(md5($password) . $salt);
	}
	
	/**
	 * 安全问题加密
	 *
	 * @param string $question
	 * @param string $answer

	 * @return bool
	 */
	public static function buildQuestion($question, $answer) {
		return substr(md5($question . $answer), 8, 8);
	}
	
	public static function appKey($apiId, $time, $secretkey) {
		return md5(md5($apiId.'||'.$secretkey).$time);
	}
	
	public static function buildRequest($url, $params = array(), $isreturn = true, $timeout = 10, $method = 'post') {
		if (function_exists('fsockopen') || function_exists('pfsockopen')) {
			Wind::import('WINDID:library.http.WindidHttpSocket');
			$request = new WindidHttpSocket($url, $timeout);
			if (!$isreturn) $request->setReturn($isreturn);
		} elseif (function_exists('curl_init')) {
			Wind::import('WINDID:library.http.WindidHttpCurl');
			$request = new WindidHttpCurl($url, $timeout);
			if (!$isreturn) $request->setReturn($isreturn);
		} else {
			return false;
		}
		if ($method == 'post') {
			return $request->post($params);
		} else {
			return $request->get($params);
		}
	}
	
	public static function buildMultiRequest($urls, $params = array()) {
		if (function_exists('curl_init')) {
			Wind::import('WINDID:library.http.WindidHttpCurl');
			foreach ($urls AS $k=>$url) {
				$request = new WindidHttpCurl($url);
				$result[$k] = $request->post($params[$k]);
			}
		} elseif (function_exists('fsockopen') || function_exists('pfsockopen')) {
			Wind::import('WINDID:library.http.WindidHttpSocket');
			foreach ($urls AS $k=>$url) {
				$request = new WindidHttpSocket($url);
				$result[$k] = $request->post($params[$k]);
			}
		} else {
			return array();
		}
		return $result;
	}
	
	public static function buildClientUrl($url, $notiFile) {
		$url = $url . '/' .$notiFile;
		$_url  = parse_url($url);
		$query = isset($_url['query']) ? '&' : '?';
		return $url. $query;
	}
}