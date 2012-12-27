<?php
/**
 * Windid工具库
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidUtility.php 22791 2012-12-27 08:11:54Z gao.wanggao $
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
	
	public static function uploadRequest($url, $file, $timeout = 30) {
		if (!function_exists('fsockopen')) {
			$urlArr = parse_url($url);
			$port = isset($urlArr['port']) ? $urlArr['port'] : 80;
        	$boundary = "---------------------".substr(md5(rand(0,32000)),0,10);
	        $header = "POST ".$urlArr['path'].'?'. $urlArr['query']." HTTP/1.0\r\n";
	        $header .= "Host: ".$urlArr['host']."\r\n";
	        $header .= "Content-type: multipart/form-data, boundary=".$boundary."\r\n";
			if (!file_exists($file)) return false;
			$imageInfo = @getimagesize($file);
			$exts = array('1'=>'gif', '2'=>'jpg', '3'=>'png');
			if (!isset($exts[$imageInfo[2]])) continue;
			$ext = $exts[$imageInfo[2]];
			$filename = rand(1000,9999). '.'.$ext;
	        $data = '';
	        $data .= "--$boundary\r\n";
	        $data .="Content-Disposition: form-data; name=\"FileData\"; filename=\"".$filename."\"\r\n";
	        $data .= "Content-Type: ".$imageInfo['mime']."\r\n\r\n";
	        $data .= WindFile::read($file)."\r\n";
	        $data .="--$boundary--\r\n";
      	 	$header .= "Content-length: " . strlen($data) . "\r\n\r\n";
       		$fp = fsockopen($urlArr['host'], $port);
        	fputs($fp, $header.$data);
        	$response = '';
		    while (!feof($fp)) {
		        $response .= fgets($fp, 128);
		    }
		    fclose($fp);
	 		preg_match("/Content-Length:.?(\d+)/", $response, $matches);
			if (isset($matches[1])) {
				$response = substr($response, strlen($response) - intval($matches[1]));
			}
			return $response;
	        
		} elseif (function_exists('curl_init')) {
			 $curl = curl_init($url);  
		     curl_setopt($curl,CURLOPT_POST, true);  
		     curl_setopt($curl,CURLOPT_POSTFIELDS, $file);
		     curl_setopt($curl,CURLOPT_TIMEOUT, $timeout);  
		     curl_setopt($curl,CURLOPT_FOLLOWLOCATION, false);  
		     curl_setopt($curl,CURLOPT_RETURNTRANSFER, true);    
		     $response = curl_exec($curl);  
			 curl_close($curl);
			 return $response;
		} else {
			return false;
		}
	}
	
	public static function buildClientUrl($url, $notiFile) {
		$url = $url . '/' .$notiFile;
		$_url  = parse_url($url);
		$query = isset($_url['query']) ? '&' : '?';
		return $url. $query;
	}
}