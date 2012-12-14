<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignImportZip.php 21891 2012-12-14 12:02:44Z gao.wanggao $ 
 * @package 
 */
class PwDesignImportZip {
	public $newIds = array();
	
	protected $themesPath = '';
	protected $pageid = 0;
	
	private $_files = array();
	private $_tplExt = '.htm';
	
	public function __construct($pageid) {
		$this->pageid = $pageid;
		$this->themesPath = Wind::getRealDir('THEMES:portal.local.');
	}
	
	/**
	 * 检查目录
	 * Enter description here ...
	 */
	public function checkDirectory() {
		if (is_writable($this->themesPath)) return true;
		return false;
	}
	
	/**
	 * 检查并导入zip文件
	 * Enter description here ...
	 * @param string $filename
	 */
	public function checkZip($filename) {
		Wind::import("WIND:parser.WindXmlParser");
		Wind::import('LIB:utility.PwZip');
		$config = array();
		$_isTpl = false;
		$extension = array('htm','js','gif','jpg','jpeg','txt','png','css','xml');
		$zip = new PwZip();
		$xml = new WindXmlParser();
		if (!$fileData = $zip->extract($filename)) return new PwError("DESIGN:upload.file.error");
		foreach ($fileData AS &$file) {
			$file['filename'] = str_replace('\\', '/', $file['filename']);
			$pos = strpos($file['filename'], '/');
			$lenth = strlen($file['filename']);
			$file['filename'] = substr($file['filename'], (int)$pos+1, $lenth-$pos);
			if (strtolower($file['filename']) == 'manifest.xml') {
				$config = $xml->parseXmlStream($file['data'], 0);
				//$_charset = strtolower($config['application']['charset']);
				//$_charset == 'gbk' && $_charset = 'gb2312';
			}
			//过滤文件类型
			$ext = strtolower(substr(strrchr($file['filename'], '.'), 1));
			if (!in_array($ext, $extension)) {
				unset($file); 
				continue;
			}
			
			//过滤中文文件名
			if (preg_match('/^[\x7f-\xff]+$/', $file['filename'])) {
				unset($file);
				continue;
			}
		}
		if (!$config) return new PwError("DESIGN:file.check.fail");
		//!$_charset && $_charset = 'utf-8';
		//$charset = strtolower(Wind::getApp()->getResponse()->getCharset());
		//$charset = ($charset == 'gbk') ? 'gb2312' : 'utf-8';
		//if ($charset != $_charset)  return new PwError("DESIGN:file.charset.fail");
		foreach ($fileData AS &$_file) {
			if ($_file['filename'] != 'template/index'.$this->_tplExt) continue;
			/*$_file['data'] = $this->compileTitle($_file['data']);
			$_file['data'] = $this->replaceTpl($_file['data'], $config);
			$_file['data'] = $this->compileTpl($_file['data']);
			$_file['data'] = $this->compilePw($_file['data']);
			$_file['data'] = $this->compileDrag($_file['data']);*/
			$_file['data'] = $this->compileStyle($_file['data']);
			if ($_file['data']) $_isTpl = true;
		}
		WindFile::del($filename);
		//TODO 版本号验证
		if (!$fileData) return new PwError("DESIGN:file.check.fail");
		if (!$_isTpl) return new PwError("DESIGN:file.check.fail");
		if (!$this->writeFile($fileData)) {
			$this->importTxt();
			return true;
		} 
		return false;
	}
	
	/**
	 * 导入应用中心模版
	 * Enter description here ...
	 * @param string $folder
	 */
	public function appcenterToLocal($folder) {
		if (!$folder) return false;
		$appPath = Wind::getRealDir('THEMES:portal.appcenter.'.$folder.'.');	
		$fileData = $this->read($appPath);	
		$ifTpl = false;
		foreach ($fileData AS &$_file) {
			$_file['filename'] = str_replace($appPath, '', $_file['filename']);
			if ($_file['filename'] != 'template/index'.$this->_tplExt) continue;
			/*$_file['data'] = $this->compileTitle($_file['data']);
			$_file['data'] = $this->replaceTpl($_file['data'], $config);
			$_file['data'] = $this->compileTpl($_file['data']);
			$_file['data'] = $this->compilePw($_file['data']);
			$_file['data'] = $this->compileDrag($_file['data']);*/
			$_file['data'] = $this->compileStyle($_file['data']);
			$ifTpl = true;
		}
		if (!$ifTpl) return false;
		if (!$this->writeFile($fileData)) {
			$this->importTxt();
			return true;
		} 
		return false;
	}
	
	protected function compileTpl($section) {
		Wind::import('SRV:design.dm.PwDesignModuleDm');
		$ds = Wekit::load('design.PwDesignModule');
    	if (preg_match_all('/\<pw-list[>|\/>](.+)<\/pw-list>/isU',$section, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			$v = str_replace("	",'', trim($v));
	    		$name = 'section_' . WindUtility::generateRandStr(6);
	    		$dm = new PwDesignModuleDm();
	    		$dm->setPageId($this->pageid)
	    			->setFlag('thread')
	    			->setName($name)
	    			->setModuleTpl($v)
	    			->setModuleType(PwDesignModule::TYPE_IMPORT)
	    			->setIsused(1);
	    		$this->newIds[] = $moduleId = $ds->addModule($dm);
	    		if ($moduleId instanceof PwError)  continue;
	    		$_html = '<design id="D_mod_'.$moduleId.'" role="module" ></design>';
	    		$section = preg_replace('/\<pw-list[>|\/>](.+)<\/pw-list>/isU', $_html, $section, 1);
    		}
    	}
	    return $section;
	}
	
	
	protected function replaceTpl($section, $config) {
		Wind::import('SRV:design.dm.PwDesignModuleDm');
		$ds = Wekit::load('design.PwDesignModule');
    	if (preg_match_all('/\<pw-list\s*id=\"(\d+)\"\s*[>|\/>](.+)<\/pw-list>/isU',$section, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			$tpl = trim($matches[2][$k]);
    			if (!isset($config['item'][$v])) $config['item'][$v] = array();
    			$item = $config['module']['item'][$v];
    			$module = $ds->getModule($item['id']);
    			if ($module && $module['module_name'] == $item['name'] && $module['module_type'] == PwDesignModule::TYPE_IMPORT && $module['isused']){
    				continue;
    			}
    			$property = array(
    				'titlenum'=>$item['titlenum'],
    				'desnum'=>$item['desnum'],
    				'timefmt'=>$item['timefmt'],
    				'limit'=>$item['limit']
    			);
    			!$item['model'] && $item['model'] = 'thread';
	    		$item['name'] = $item['name'] ? $item['name'] : 'T_module_' . WindUtility::generateRandStr(6);
	    		$dm = new PwDesignModuleDm();
	    		$dm->setPageId($this->pageid)
	    			->setFlag($item['model'])
	    			->setName($item['name'])
	    			->setModuleTpl($tpl)
	    			->setProperty($property)
	    			->setModuleType(PwDesignModule::TYPE_IMPORT)
	    			->setIsused(1);
	    		$this->newIds[] = $moduleId = $ds->addModule($dm);
	    		if ($moduleId instanceof PwError)  continue;
	    		$_html = '<design id="D_mod_'.$moduleId.'" role="module"></design>';
	    		$section = preg_replace('/\<pw-list\s*id=\"(\d+)\"\s*[>|\/>](.+)<\/pw-list>/isU', $_html, $section, 1);
	    		
	    		//$section = str_replace($matches[0][$k], $_html, $section);
    		}
    	}
	    return $section;
	}
	
	protected function compileTitle($section) {
		Wind::import('SRV:design.dm.PwDesignStructureDm');
		$ds = Wekit::load('design.PwDesignStructure');
    	if (preg_match_all('/\<pw-title[>|\/>](.+)<\/pw-title>/isU',$section, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			$v = trim($v);
	    		$name = 'T_'.WindUtility::generateRandStr(6);
	    		$dm = new PwDesignStructureDm();
	    		$dm->setStructTitle($v)
	    			->setStructName($name);
	    		$resource = $ds->replaceStruct($dm);
	    		if ($resource instanceof PwError)  continue;
	    		$_html = '<design role="title" id="'.$name.'"/>';
	    		//$section = str_replace($matches[0][$k], $_html, $section);
	    		$section = preg_replace('/\<pw-title[>|\/>](.+)<\/pw-title>/isU', $_html, $section, 1);
    		}
    	}
	    return $section;
	}
	
	protected function compilePw($section) {
		$in = array(
			'<pw-start>',
			'<pw-head>',
			'<pw-navigate>',
			'<pw-footer>',
			'<pw-end>',
			'<pw-drag>'
		);
		$out = array(
			'<pw-start/>',
			'<pw-head/>',
			'<pw-navigate/>',
			'<pw-footer/>',
			'<pw-end/>',
			'<pw-drag/>'
		);
		$section = str_replace($in, $out, $section);
		$in = array(
			'<pw-start/>',
			'<pw-head/>',
			'<pw-navigate/>',
			'<pw-footer/>',
			'<pw-end/>',
		);
		$out = array(
			'<design role="start"/>',
			'<!--# if($portal[\'header\']): #--><template source=\'TPL:common.header\' load=\'true\' /><!--# endif; #-->',
			'<!--# if($portal[\'navigate\']): #--><div class="bread_crumb">{@$headguide|html}</div><!--# endif; #-->',
			'<!--# if($portal[\'footer\']): #--><template source=\'TPL:common.footer\' load=\'true\' /><!--# endif; #-->',
			'<design role="end"/>',
		);
		return str_replace($in, $out, $section);
	}
	
	protected function compileDrag($section) {
		if (preg_match_all('/\<pw-drag\/>/isU',$section, $matches)) {
    		foreach ($matches[0] AS $k=>$v) {
    			$_html = '<design role="segment" id="'.WindUtility::generateRandStr(8).'"/>';
    			$section = preg_replace('/\<pw-drag\/>/isU', $_html, $section, 1);
    		}
		}
		return $section;
	}
	
	/**
	 * 给style.css 加个随机码
	 * Enter description here ...
	 * @param unknown_type $section
	 */
	protected function compileStyle($section) {
		$in = '{@G:design.url.css}/style.css';
		$out = '{@G:design.url.css}/style.css?rand='.Pw::getTime();
		return str_replace($in, $out, $section);
	}
	
	protected function importTxt() {
		$dir = $this->themesPath.$this->pageid.'/txt';
		$this->_files = array();
		$fileData = $this->read($dir);
		$srv = Wekit::load('design.srv.PwDesignImportTxt');
		$pageInfo = $this->_getPageDs()->getPage($this->pageid);
		foreach ($fileData AS $_file) {
			$ext = strtolower(substr(strrchr($_file['filename'], '.'), 1));
			if ($ext != 'txt') continue;
			$srv = new PwDesignImportTxt();
			$srv->setPageInfo($pageInfo);
			$resource = $srv->checkTxt($_file['filename']);
			if ($resource instanceof PwError) continue;
			$resource = $srv->importTxt();
			if ($resource instanceof PwError) {
				$srv->rollback();
				continue;
			}
		}
	
		foreach ($srv->newIds AS $id) {
			if (!$id) continue;
			$this->newIds[] = $id;
		}
		WindFolder::rm($dir, true);
	}
	
	protected function writeFile($fileData) {
		$failArray = array();
		$dir = $this->themesPath . $this->pageid;
		WindFolder::rm($dir, true);
		WindFolder::mk($dir);
		foreach ($fileData AS $file) {
			WindFolder::mkRecur($dir . '/' . dirname($file['filename']));
			if (!WindFile::write($dir . '/' . $file['filename'], $file['data'])) $failArray[] = $file['filename'];
		}
		return $failArray;
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
			} elseif (is_dir($fileName)){
				$this->read($fileName .'/');
			}
		}
		unset($data);
		@closedir($handle);
		return $this->_files;
	}
	
	private function _getPageDs() {
		return Wekit::load('design.PwDesignPage');
	}
}
?>