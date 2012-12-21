<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwPortalCompile.php 22377 2012-12-21 14:28:48Z gao.wanggao $ 
 * @package 
 */
class PwPortalCompile {
		
	private $dir = '';
	private $pageid = 0;
	private $isCompile = false;
	
	public function __construct($pageid) {
		$this->pageid = $pageid;
		$this->dir = Wind::getRealDir('THEMES:portal.local.'.$this->pageid . '.template.');
	}
	
	/**
	 * 生成门户模版
	 * Enter description here ...
	 * @param $compileStr
	 */
	public function compilePortal($compileStr) {
		$file = $this->dir . 'index.htm';
		$content = $this->read($file);
		if ($content === false) {
			return true;
		}
		$content = preg_replace('/\<pw-start\/>(.+)<pw-end\/>/isU', $compileStr, $content);
		if ($this->isCompile) $this->write($content, $file);
		return true;
	}
	
	/**
	 * 对标签进行编译
	 * Enter description here ...
	 * @param unknown_type $content
	 */
	public function compileDesign($content, $segment = '') {
		$this->isCompile = false;
		$content = $this->compilePw($content);
		$content = $this->updateTitle($content);
		$content = $this->updateList($content);
		$content = $this->compileDrag($content);
		$content = $this->compileList($content, $segment);
		return $this->compileTitle($content, $segment);
	}
	
	/**
	 * 对pw-tpl标签进行编译
	 * Enter description here ...
	 * @param unknown_type $tplId
	 */
	public function compileTpl($section) {
		if (preg_match_all('/\<pw-tpl\s*id=\"(\w+)\"\s*\/>/isU',$section, $matches)) {
			$ds = Wekit::load('design.PwDesignSegment');
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
				$file = $this->dir . $v. '.htm';
				if (!WindFile::isFile($file)) {
					WindFolder::mkRecur($this->dir);
					WindFolder::mkRecur(dirname($this->dir) . '/images/');
					WindFolder::mkRecur(dirname($this->dir) . '/css/');
					$this->write('<pw-drag id="'.$v.'"/>', $file);
				}
				
				$xmlFile = dirname($this->dir) . '/Manifest.xml';
				if (!WindFile::isFile($xmlFile)) {
					$fromFile = Wind::getRealDir('TPL:special.default.') . 'Manifest.xml';
					@copy($fromFile, $xmlFile);
					@chmod($xmlFile, 0777);
				}
    			$content = $this->read($file);
			   	$content = $this->compileDesign($content, $v);
			    $ds->replaceSegment($v. '__tpl', $this->pageid,'', $content);
				if ($this->isCompile) $this->write($content, $file);
				$section = str_replace($matches[0][$k], $content, $section);
    		}
		}
		return $section;
	}
    
	/**
	 * 修改模块
	 * Enter description here ...
	 * @param int $id
	 * @param string $repace
	 */
	public function replaceList($id, $repace, $file = 'index') {
		if (!$file) return false;
		$file = $this->dir . $file . '.htm';
		$content = $this->read($file);
		if (preg_match_all('/\<pw-list\s*id=\"(\d+)\"\s*[>|\/>](.+)<\/pw-list>/isU',$content, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			if ($v != $id) continue;
	    		$_html = '<pw-list id="'.$id.'">'.$repace.'</pw-list>';
	    		$content = str_replace($matches[0][$k], $_html, $content);
    		}
    	}
		$this->write($content, $file);
	}

	/**
	 * 修改主标题
	 * Enter description here ...
	 * @param string $name
	 * @param string $repace
	 */
	public function replaceTitle($name, $repace, $file = 'index') {
		if (!$file) return false;
		$file = $this->dir . $file . '.htm';
		$content = $this->read($file);
		if (preg_match_all('/\<pw-title\s*id=\"(\w+)\"\s*[>|\/>](.+)<\/pw-title>/isU',$content, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			if ($v != $name) continue;
	    		$_html = '<pw-title id="'.$name.'">'.$repace.'</pw-list>';
	    		$content = str_replace($matches[0][$k], $_html, $content);
    		}
    	}
		$this->write($content, $file);
	}
	
	public function restoreTpl($file, $content) {
		$file = $this->dir . $file . '.htm';
		return $this->write($content, $file);
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
		return str_replace($in, $out, $section);
	}
	
	protected function updateTitle($section) {
		if (preg_match_all('/\<pw-title\s*id=\"(\w+)\"\s*[>|\/>](.+)<\/pw-title>/isU',$section, $matches)) {
			Wind::import('SRV:design.dm.PwDesignStructureDm');
			$ds = Wekit::load('design.PwDesignStructure');
			foreach ($matches[1] AS $k=>$v) {
	    		$dm = new PwDesignStructureDm();
	    		$dm->setStructTitle($matches[2][$k])
	    			->setStructName($v);
	    		$ds->editStruct($dm);
    		}
		}
		return $section;
	}
	
	protected function updateList($section) {
		if (preg_match_all('/\<pw-list\s*id=\"(\d+)\"\s*[>|\/>](.+)<\/pw-list>/isU',$section, $matches)) {
			Wind::import('SRV:design.dm.PwDesignModuleDm');
			$ds = Wekit::load('design.PwDesignModule');
			foreach ($matches[1] AS $k=>$v) {
	    		$dm = new PwDesignModuleDm($v);
	    		$dm->setModuleTpl($matches[2][$k]);
	    		$ds->updateModule($dm);
    		}
		}
		return $section;
	}
	
	
	
	protected function compileList($section, $segment = '') {
		Wind::import('SRV:design.dm.PwDesignModuleDm');
		$ds = Wekit::load('design.PwDesignModule');
    	if (preg_match_all('/\<pw-list[>|\/>](.+)<\/pw-list>/isU',$section, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			$v = str_replace("	",'', trim($v));
	    		$name = 'section_' . $this->getRand(6);
	    		$dm = new PwDesignModuleDm();
	    		$dm->setPageId($this->pageid)
	    			->setSegment($segment)
	    			->setFlag('thread')
	    			->setName($name)
	    			->setModuleTpl($v)
	    			->setModuleType(PwDesignModule::TYPE_IMPORT)
	    			->setIsused(1);
	    		$moduleId = $ds->addModule($dm);
	    		if ($moduleId instanceof PwError)  continue;
	    		$_html = '<pw-list id="'.$moduleId.'">\\1</pw-list>';
	    		$section = preg_replace('/\<pw-list[>|\/>](.+)<\/pw-list>/isU', $_html, $section, 1);
    		}
    		$this->isCompile = true;
    	}
	    return $section;
	}
	
	protected function compileTitle($section, $segment = '') {
		Wind::import('SRV:design.dm.PwDesignStructureDm');
		$ds = Wekit::load('design.PwDesignStructure');
    	if (preg_match_all('/\<pw-title[>|\/>](.+)<\/pw-title>/isU',$section, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			$v = trim($v);
	    		$name = 'T_'.$this->getRand(6);
	    		$dm = new PwDesignStructureDm();
	    		$dm->setStructTitle($v)
	    			->setStructName($name)
	    			->setSegment($segment);
	    		$resource = $ds->replaceStruct($dm);
	    		if ($resource instanceof PwError)  continue;
	    		$_html = '<pw-title id="'.$name.'">\\1</pw-title>';
	    		//$section = str_replace($matches[0][$k], $_html, $section);
	    		$section = preg_replace('/\<pw-title[>|\/>](.+)<\/pw-title>/isU', $_html, $section, 1);
    		}
    		$this->isCompile = true;
    	}
	    return $section;
	}
	
	protected function compileDrag($section) {
		if (preg_match_all('/\<pw-drag\/>/isU',$section, $matches)) {
    		foreach ($matches[0] AS $k=>$v) {
    			$_html = '<pw-drag id="'.$this->getRand(8).'"/>';
    			$section = preg_replace('/\<pw-drag\/>/isU', $_html, $section, 1);
    		}
    		$this->isCompile = true;
		}
		return $section;
	}
	
	protected function getRand($length) {
		$mt_string = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
		$randstr = '';
		for ($i = 0; $i < $length; $i++) {
			$randstr .= $mt_string[mt_rand(0, 52)];
		}
		return $randstr;
	}
	
	protected function write($content, $file) {
		return WindFile::write($file, $content);
	}
	
	protected function read($file) {
		return WindFile::read($file);
	}
	
	
}
?>