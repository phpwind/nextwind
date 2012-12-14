<?php
Wind::import('APPS:appcenter.service.srv.helper.PwApplicationHelper');
/**
 * 系统升级帮助类
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwSystemHelper.php 21636 2012-12-12 03:41:00Z long.shi $
 * @package wind
 */
class PwSystemHelper {

	/**
	 * 解析sql语句，并返回解析后的结果
	 *
	 * @param string $strSQL        	
	 * @param string $charset        	
	 * @param string $dbprefix        	
	 * @return array($sqlStatement,$sqlOptions)
	 */
	static public function sqlParser($strSQL, $charset, $dbprefix, $engine) {
		if (empty($strSQL)) return array();
		
		$dataSQL = array();
		$strSQL = str_replace(array("\r", "\n", "\r\n"), "\n", $strSQL);
		$arrSQL = explode("\n", $strSQL);
		$query = '';
		$i = 0;
		foreach ($arrSQL as $value) {
			$value = trim($value, " \t");
			if (!$value || substr($value, 0, 2) === '--') continue;
			$query .= $value;
			if (substr($query, -1) != ';') continue;
			$sql_key = strtoupper(substr($query, 0, strpos($query, ' ')));
			$query = trim(preg_replace('/([ `]+)pw_/', '$1' . $dbprefix, $query, 1), ';');
			if ($sql_key == 'CREATE') {
				$query = preg_replace('/\)([\w\s=]*);/i', 
					')ENGINE=' . $engine . ' DEFAULT CHARSET=' . $charset, $query);
				$dataSQL[$i][] = $query;
			} else if ($sql_key == 'DROP') {
				$dataSQL[$i][] = $query;
			} else if ($sql_key == 'ALTER') {
				++$i;
				$dataSQL[$i][] = $query;
				++$i;
			} elseif (in_array($sql_key, array('INSERT', 'REPLACE', 'UPDATE', 'DELETE'))) {
				$dataSQL[$i][] = $query;
			}
			$query = '';
		}
		return $dataSQL;
	}

	public static function download($url, $file) {
		Wind::import('WIND:http.transfer.WindHttpCurl');
		$http = new WindHttpCurl($url);
		WindFolder::mkRecur(dirname($file));
		$fp = fopen($file, "w");
		$opt = array(
			CURLOPT_FILE => $fp, 
			CURLOPT_HEADER => 0, 
			CURLOPT_FOLLOWLOCATION => true, 
			CURLOPT_SSL_VERIFYPEER => false, 
			CURLOPT_SSL_VERIFYHOST => false);
		$http->send('GET', $opt);
		if ($e = $http->getError()) return array(false, $e);
		$http->close();
		fclose($fp);
		return array(true, $file);
	}

	/**
	 * 根据升级列表校对md5
	 *
	 * 返回有更改的/无更改的/新增的
	 */
	public static function validateMd5($fileList) {
		$root = Wind::getRealDir('ROOT:');
		$change = $unchange = $new = array();
		foreach ($fileList as $f => $hash) {
			$file = $root . DIRECTORY_SEPARATOR . $f;
			if (!file_exists($file)) {
				$new[] = $f;
				continue;
			}
			if (md5_file($file) != $hash)
				$change[] = $f;
			else
				$unchange[] = $f;
		}
		return array($change, $unchange, $new);
	}
	
	/**
	 * 解压压缩包,将源文件解压至目标文件
	 * 目前只支持zip文件的解压，返回解后包文件绝对路径地址
	 *
	 * @param string $source        	
	 * @param string $target        	
	 * @return string
	 */
	static public function extract($source, $target) {
		Wind::import('APPS:appcenter.service.srv.helper.PwExtractZip');
		$zip = new PwExtractZip();
		if (!$data = $zip->extract($source)) return false;
		foreach ($data as $value) {
			$filename = $target . '/' . $value['filename'];
			WindFolder::mkRecur(dirname($filename));
			WindFile::write($filename, $value['data']);
		}
		return true;
	}

	/**
	 * 检查升级文件目录可写
	 *
	 * @param unknown_type $fileList        	
	 * @return multitype:boolean unknown |boolean
	 */
	public static function checkFolder($fileList) {
		foreach ($fileList as $v => $hash) {
			$root = Wind::getRealDir('ROOT:');
			$file = $root . DIRECTORY_SEPARATOR . $v;
			WindFolder::mkRecur(dirname($file));
			if (!self::checkWriteAble($file)) return array(false, $v);
		}
		return true;
	}

	public static function log($msg, $version, $start = false) {
		static $log;
		if (!$log) {
			$log = Wind::getRealDir('DATA:upgrade.log', true) . '/' . $version . '.log';
			WindFolder::mkRecur(dirname($log));
		}
		$status = $start ? WindFile::READWRITE : WindFile::APPEND_WRITEREAD;
		WindFile::write($log, "\r\n" . date('Y-m-d H:i') . '   ' . $msg, $status);
	}

	/**
	 * 检查目录可写
	 *
	 * @param string $pathfile        	
	 * @return boolean
	 */
	public static function checkWriteAble($pathfile) {
		if (!$pathfile) return false;
		$isDir = substr($pathfile, -1) == '/' ? true : false;
		if ($isDir) {
			if (is_dir($pathfile)) {
				mt_srand((double) microtime() * 1000000);
				$pathfile = $pathfile . 'pw_' . uniqid(mt_rand()) . '.tmp';
			} elseif (@mkdir($pathfile)) {
				return self::checkWriteAble($pathfile);
			} else {
				return false;
			}
		}
		$exist = file_exists($pathfile);
		@chmod($pathfile, 0777);
		$fp = @fopen($pathfile, 'ab');
		if ($fp === false) return false;
		fclose($fp);
		$exist || @unlink($pathfile);
		return true;
	}
	
	public static function relative($relativePath) {
		$pattern = '/\w+\/\.\.\/?/';
		while(preg_match($pattern,$relativePath)){
			$relativePath = preg_replace($pattern, '', $relativePath);
		}
		return $relativePath;
	}
	
	public static function replaceStr($str, $search, $replace, $count, $nums) {
		$strarr = explode($search, $str);
		$replacestr = '';
		foreach ($strarr as $key => $value) {
			if ($key == $count) {
				$replacestr .= $value;
			} else {
				if (in_array(($key + 1), $nums)) {
					$replacestr .= $value . $replace;
				} else {
					$replacestr .= $value . $search;
				}
			}
		}
		return $replacestr;
	}
}

?>