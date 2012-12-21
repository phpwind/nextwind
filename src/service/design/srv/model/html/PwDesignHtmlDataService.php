<?php
Wind::import('SRV:design.srv.model.PwDesignModelBase');
/**
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignHtmlDataService.php 22107 2012-12-19 08:04:08Z gao.wanggao $ 
 * @package 
 */
class PwDesignHtmlDataService extends PwDesignModelBase{
	
	public function decorateAddProperty($model) {
		return array();
	}
	
	public function decorateEditProperty($moduleBo) {
		return array();
	}
	
	public function decorateSaveProperty($property) {
		if(Pw::strlen($property['html']) > 10000) return new PwError('DESIGN:html.length.error');
		$property['html_tpl'] = $property['html'];
		$property['limit'] = 1;
		return $property;
	}
	
	protected  function getData($field, $order, $limit, $offset) {
		$data[0]['html'] = $field['html'];
		return $data;
	}
	
}
?>