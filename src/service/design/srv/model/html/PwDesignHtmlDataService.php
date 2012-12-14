<?php
Wind::import('SRV:design.srv.model.PwDesignModelBase');
/**
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignHtmlDataService.php 16761 2012-08-28 07:20:37Z gao.wanggao $ 
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