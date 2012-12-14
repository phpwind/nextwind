<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignDisplay.php 20647 2012-11-01 08:44:25Z gao.wanggao $ 
 * @package 
 */
class PwDesignDisplay {
	
	/**
	 * 获取模块展示数据
	 * 
	 * @param int $moduleId  模块ID
	 * @param bool $isextend  是否包括扩展数据
	 * @param bool $isreserv  是否包括预定数据
	 */
	public function getModuleData($moduleId, $isextend = true, $isreserv = false) {
		if ($moduleId < 1) return false;
		$time = Pw::getTime();
		$delDataid = $extend = array();
		$ds = Wekit::load('design.PwDesignData');
		$data = $ds->getDataByModuleid($moduleId);
		foreach ($data AS $k=>$v) {
			if (!$isreserv && $v['is_reservation']) continue;
			$_tmp = unserialize($v['extend_info']);
			$standard = unserialize($v['standard']);
			list($bold, $underline, $italic, $color) = explode('|', $v['style']);
			$_tmp['__style'] = $this->_formatStyle($bold, $underline, $italic, $color);
			unset($_tmp['standard_image']);
			$data[$k]['title'] = $_tmp[$standard['sTitle']];
			$data[$k]['url'] = $_tmp[$standard['sUrl']];
			$data[$k]['intro'] = $_tmp[$standard['sIntro']];
			$extend[] = $_tmp;
    	}
    	if ($isextend) return $extend;
    	return $data;
	}
	
	public function bindDataKey($moduleId) {
		return 'J_mod_'.$moduleId;
	}
	
	private function _formatStyle($bold = '', $underline = '', $italic = '', $color = '') {
		if ($bold) $style = 'font-weight:bold;';
		if ($underline) $style .= 'text-decoration:underline;';
		if ($italic) $style .= 'font-style:italic;';
		if ($color) $style .= 'color:'.$color;
		return $style ?  $style  : '';
	}
}


?>