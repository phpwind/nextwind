<?php
Wind::import('WINDID:library.http.AbstractWindidHttp');
/**
 * @author Qian Su <aoxue.1988.su.qian@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com/license.php
 * @version $Id: WindidHttpCurl.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package http
 * @subpackage transfer
 */
class WindidHttpCurl extends AbstractWindidHttp{

	/**
	 * @return mixed
	 */
	public function getInfo() {
		return curl_getinfo($this->httpHandler);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::createHttpHandler()
	 */
	protected function createHttpHandler() {
		return curl_init();
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::request()
	 */
	public function request($name, $value = null) {
		curl_setopt($this->httpHandler, $name, $value);
	}
	
	public function execute() {
		return curl_exec($this->httpHandler);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::response()
	 */
	public function response() {
		return curl_multi_getcontent($this->httpHandler);
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::close()
	 */
	public function close() {
		if (null === $this->httpHandler) return;
		curl_close($this->httpHandler);
		$this->httpHandler = null;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::getError()
	 */
	public function getError() {
		$this->err = curl_error($this->httpHandler);
		$this->eno = curl_errno($this->httpHandler);
		return $this->err ? $this->eno . ':' . $this->err : '';
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::send()
	 */
	public function send($method = 'GET', $options = array()) {
		if ($this->data) {
			switch (strtoupper($method)) {
				case 'GET':
					$_url = WindUrlHelper::argsToUrl($this->data);
					$url = parse_url($this->url);
					$this->url .= (isset($url['query']) ? '&' : '?') . $_url;
					break;
				case 'POST':
					$this->request(CURLOPT_POST, 1);
					$_url = WindUrlHelper::argsToUrl($this->data, false);
					$this->request(CURLOPT_POSTFIELDS, $_url);
					break;
				default:
					break;
			}
		}
		
		$this->request(CURLOPT_HEADER, $this->_header);
		$this->request(CURLOPT_NOBODY, !$this->_body);
		$this->request(CURLOPT_TIMEOUT, $this->timeout);
		$this->request(CURLOPT_FOLLOWLOCATION, 0);
		$this->request(CURLOPT_RETURNTRANSFER, 1);
		if ($options && is_array($options)) {
			curl_setopt_array($this->httpHandler, $options);
		}
		$_cookie = '';
		foreach ($this->cookie as $key => $value) {
			$_cookie .= ($_cookie !== '' ? "" : "; ") . $key . "=" . $value;
		}
		$this->request(CURLOPT_COOKIE, $_cookie);
		
		$this->setHeader('Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)', 'User-Agent');
		$_header = array();
		foreach ($this->header as $key => $value) {
			$_header[] = $key . ": " . $value;
		}
		$_header && $this->request(CURLOPT_HTTPHEADER, $_header);
		$this->request(CURLOPT_URL, $this->url);
		if (isset($options[CURLOPT_FOLLOWLOCATION])) $this->_redirects = $options[CURLOPT_FOLLOWLOCATION];
		if (isset($options[CURLOPT_MAXREDIRS])) $this->_maxRedirs = intval($options[CURLOPT_MAXREDIRS]);
		$this->followLocation();
		$this->execute();
		if ($this->isReturn) {
			return $this->response();
		} else {
			return true;
		}
	}
	
	/* (non-PHPdoc)
	 * @see AbstractWindHttp::getStatus()
	 */
	public function getStatus() {
		return curl_getinfo(CURLINFO_HTTP_CODE);
	}

	/**
	 * url forward 兼容处理
	 */
	private function followLocation() {
		$_safeMode = ini_get('safe_mode');
		if (ini_get('open_basedir') == '' && ($_safeMode == '' || strcasecmp($_safeMode, 'off') == 0)) return;
		if (!$this->_redirects) return;
		if ($this->_maxRedirs <= 0) return;
		$maxRedirs = $this->_maxRedirs;
		
		$newurl = curl_getinfo($this->httpHandler, CURLINFO_EFFECTIVE_URL);
		$rch = curl_copy_handle($this->httpHandler);
		curl_setopt($rch, CURLOPT_HEADER, true);
		curl_setopt($rch, CURLOPT_NOBODY, true);
		curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
		curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);
		do {
			curl_setopt($rch, CURLOPT_URL, $newurl);
			$header = curl_exec($rch);
			
			if (curl_errno($rch)) {
				$code = 0;
			} else {
				$code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
				if ($code == 301 || $code == 302) {
					preg_match('/Location:(.*?)\n/', $header, $matches);
					$newurl = trim(array_pop($matches));
				} else {
					$code = 0;
				}
			}
		} while ($code && --$maxRedirs);
		curl_close($rch);
		curl_setopt($this->httpHandler, CURLOPT_URL, $newurl);
	}
}

