<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignExportZip.php 21891 2012-12-14 12:02:44Z gao.wanggao $ 
 * @package 
 */
class PwDesignExportZip {
	protected $dir = '';
	protected $pageid = 0;
	
	private $_tplExt = '.htm';
	private $_files = array();
	private $_moduleConf = array();
	
	public function __construct($pageid) {
		$this->pageid = $pageid;
		$this->dir = Wind::getRealDir('THEMES:portal.local.'). $pageid . '/';
	}
	
	public function zip() {
		Wind::import('LIB:utility.PwZip');
		$zip = new PwZip();
		$files = $this->read($this->dir);
		foreach ($files AS &$v) {
			$v['filename'] = str_replace($this->dir, '', $v['filename']);
			if ($v['filename'] != 'template/index'.$this->_tplExt) continue;
			
			/*$v['data'] = $this->decompilePw($v['data']);
			$v['data'] = $this->decompileStruct($v['data']);
			$v['data'] = $this->decompileSegment($v['data']);
			$v['data'] = $this->decompileTpl($v['data']);*/
			$v['data'] = $this->decompileStyle($v['data']);
		}
		foreach ($files AS $file) {
			if (strtolower($file['filename']) == 'manifest.xml') {
				Wind::import("WIND:parser.WindXmlParser");
				$xml = new WindXmlParser();
				$config = $xml->parseXmlStream($file['data'], 0);
				unset($config['module']);
				$config = array_merge($config, array('module'=>$this->_moduleConf));
				$file['data'] = $this->xmlFormat($config);
			}
			$file['filename'] = 'phpwind9.0/'.$file['filename'];
			if (!$zip->addFile($file['data'], $file['filename'])) return new PwError("DESIGN:zlib.error");
		}
		$txt = $this->doTxt();
		$txtfile = 'phpwind9.0/txt/'.$txt['filename'].'.txt';
		$zip->addFile($txt['content'], $txtfile);
		return $zip->getCompressedFile();
	}
	
	protected function decompileTpl($section) {
		Wind::import("SRV:design.bo.PwDesignModuleBo");
		if(preg_match_all('/\<design\s*id=\"*D_mod_(\d+)\"*\s*role=\"*module\"*\s*[>|\/>]<\/design>/isU', $section, $matches)) {	
			foreach ($matches[1] AS $k=>$v) {
				$bo = new PwDesignModuleBo($v);
				$module = $bo->getModule();
				$property = $bo->getView();
				$_html = '<pw-list id="'.$k.'">';
				$_html .= $bo->getTemplate();
				$_html .= '</pw-list>';
				$section = str_replace($matches[0][$k], $_html, $section);
				$this->_moduleConf[$k] = array(
					'itemid'=>$k,
					'name'=>$module['module_name'],
					'model'=>$bo->getModel(), 
					'id'=>$v,
					'titlenum'=>strval($property['titlenum']),
					'desnum'=>strval($property['desnum']),
					'timefmt'=>$property['timefmt'],
					'limit'=>strval($property['limit']));
			}
		}
		return $section;
	}
	
	protected function decompilePw($section) {
		$in = array(
			'<design role="start"/>',
			'<!--# if($portal[\'header\']): #--><template source=\'TPL:common.header\' load=\'true\' /><!--# endif; #-->',
			'<!--# if($portal[\'navigate\']): #--><div class="bread_crumb">{@$headguide|html}</div><!--# endif; #-->',
			'<!--# if($portal[\'footer\']): #--><template source=\'TPL:common.footer\' load=\'true\' /><!--# endif; #-->',
			'<design role="end"/>',
		);
		$out = array(
			'<pw-start/>',
			'<pw-head/>',
			'<pw-navigate/>',
			'<pw-footer/>',
			'<pw-end/>',
		);
		return str_replace($in, $out, $section);
	}

	protected function decompileSegment($section) {
		$segment = $this->_getSegmentDs()->getSegmentByPageid($this->pageid);
		if(preg_match_all('/\<design\s*role=\"segment\"\s*id=\"(.+)\"[^>]+>/isU', $section, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
				if (isset($segment[$v])) {
					$section = str_replace($matches[0][$k], $segment[$v]['segment_struct'], $section);
				} else {
					$section = str_replace($matches[0][$k], '<pw-drag/>', $section);
				}
			}
		}
		return $section;
	}
	
	protected function decompileStruct($section) {
		$ds = Wekit::load('design.PwDesignStructure');
		if(preg_match_all('/\<design\s*role=\"title\"\s*id=\"(.+)\"[^>]+>/isU', $section, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
				$struct = $ds->getStruct($v);
				$_html = '<pw-title>';
				$_html .= unserialize($struct['struct_title']);
				$_html .= '</pw-title>';
				$section = str_replace($matches[0][$k], $_html, $section);
			}
		}
		return $section;
	}

	protected function decompileStyle($section) {
		$in = '/["|\']{\@G\:design\.url\.css}\/style\.css\?rand\=(\d+)["|\']/U';
		$out = '"{@G:design.url.css}/style.css"';
		return preg_replace($in, $out, $section);
	}
	
	protected function read($dir) {
		if (!is_dir($dir)) return array();
		if (!$handle = @opendir($dir)) return array();
		while (false !== ($file = @readdir($handle))) {
			if ('.' === $file || '..' === $file) continue;
			$fileName = $dir . $file;
			if (is_file($fileName)){
				if (!$_handle = fopen($fileName, 'rb')) continue;
				$data = '';
				while (!feof($_handle))
					$data .= fgets($_handle, 4096);
				fclose($_handle);
				$this->_files[] = array('filename'=>$fileName, 'data'=>$data);
			}elseif (is_dir($fileName . '/')) {
				$this->read($fileName. '/');
			}
		}
		unset($data);
		@closedir($handle);
		return $this->_files;
	}
	
	protected function doTxt() {
		$pageInfo = $this->_getPageDs()->getPage($this->pageid);
		Wind::import('SRV:design.srv.PwDesignExportTxt');
		$srv = new PwDesignExportTxt($pageInfo);
		return $srv->txt();
	}
	
	protected function xmlFormat($array) {
    	$dom = new DOMDocument('1.0', 'utf-8');
        $root = $dom->createElement('manifest');
        $dom->appendChild($root);
        $this->_creatDom($root, $dom, $array);
		return $dom->saveXML();	
	}
	
	private function _creatDom($root, $dom, $array) {
		$charset = strtolower(Wind::getApp()->getResponse()->getCharset());
		$charset = ($charset == 'gbk') ? 'gb2312' : 'utf-8';
		foreach ($array AS $k=>$v) {
			if (is_numeric($k)) {
				$child = $dom->createElement('item');
			} else {
				$child = $dom->createElement($k);
			}
			$root->appendChild($child);
			if (!is_array($v)){
				$v = WindConvert::convert($v, 'utf8', $charset);
				$child->appendChild($dom->createTextNode($v));
			} else {
				$this->_creatDom($child, $dom, $v);
			}
		}
	}
	
	private function _getSegmentDs() {
		return Wekit::load('design.PwDesignSegment');
	}
	
	private function _getPageDs() {
		return Wekit::load('design.PwDesignPage');
	}
}
?>