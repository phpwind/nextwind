<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwPortalCompile.php 21918 2012-12-17 03:58:49Z gao.wanggao $ 
 * @package 
 */
class PwPortalCompile {
		
	private $tpl = '';
	private $pageid = 0;
	private $isCompile = false;
	
	public function __construct($pageid) {
		$this->pageid = $pageid;
		$this->tpl = Wind::getRealDir('THEMES:portal.local.'.$this->pageid . '.template.') . 'index.htm';
	}
	
	public function compilePortal() {
		$content = $this->read();
		$content = $this->compilePw($content);
		$content = $this->updateTitle($content);
		$content = $this->updateList($content);
		$content = $this->compileDrag($content);
		$content = $this->compileList($content);
		$content = $this->compileTitle($content);
		if ($this->isCompile) $this->write($content);
	}
	
	public function compileDesign($content) {
		$content = $this->compilePw($content);
		$content = $this->updateTitle($content);
		$content = $this->updateList($content);
		$content = $this->compileDrag($content);
		$content = $this->compileList($content);
		return $this->compileTitle($content);
	}
	
	public function replaceList($id, $repace) {
		$content = $this->read();
		if (preg_match_all('/\<pw-list\s*id=\"(\d+)\"\s*[>|\/>](.+)<\/pw-list>/isU',$content, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			if ($v != $id) continue;
	    		$_html = '<pw-list id="'.$id.'">'.$repace.'</pw-list>';
	    		$content = str_replace($matches[0][$k], $_html, $content);
    		}
    	}
		$this->write($content);
	}
	
	public function replaceTitle($name, $repace) {
		$content = $this->read();
		if (preg_match_all('/\<pw-title\s*id=\"(\w+)\"\s*[>|\/>](.+)<\/pw-title>/isU',$content, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			if ($v != $name) continue;
	    		$_html = '<pw-title id="'.$name.'">'.$repace.'</pw-list>';
	    		$content = str_replace($matches[0][$k], $_html, $content);
    		}
    	}
		$this->write($content);
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
	    		$dm->setStructTitle($matches[2][0])
	    			->setStructName($v);
	    		$ds->replaceStruct($dm);
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
	    		$dm->setModuleTpl($matches[2][0]);
	    		$ds->updateModule($dm);
    		}
		}
		return $section;
	}
	
	
	
	protected function compileList($section) {
		Wind::import('SRV:design.dm.PwDesignModuleDm');
		$ds = Wekit::load('design.PwDesignModule');
    	if (preg_match_all('/\<pw-list[>|\/>](.+)<\/pw-list>/isU',$section, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			$v = str_replace("	",'', trim($v));
	    		$name = 'section_' . $this->getRand(6);
	    		$dm = new PwDesignModuleDm();
	    		$dm->setPageId($this->pageid)
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
	
	protected function compileTitle($section) {
		Wind::import('SRV:design.dm.PwDesignStructureDm');
		$ds = Wekit::load('design.PwDesignStructure');
    	if (preg_match_all('/\<pw-title[>|\/>](.+)<\/pw-title>/isU',$section, $matches)) {
    		foreach ($matches[1] AS $k=>$v) {
    			$v = trim($v);
	    		$name = 'T_'.$this->getRand(6);
	    		$dm = new PwDesignStructureDm();
	    		$dm->setStructTitle($v)
	    			->setStructName($name);
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
	
	protected function write($content) {
		return WindFile::write($this->tpl, $content);
	}
	
	protected function read() {
		return WindFile::read($this->tpl);
	}
}
?>