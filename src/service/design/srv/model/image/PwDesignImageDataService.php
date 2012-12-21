<?php
Wind::import('SRV:design.srv.model.PwDesignModelBase');
/**
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignImageDataService.php 21979 2012-12-17 12:36:08Z gao.wanggao $ 
 * @package 
 */
class PwDesignImageDataService extends PwDesignModelBase{
	
	public function decorateAddProperty($model) {
		return array();
	}
	
	public function decorateEditProperty($moduleBo) {
		return array();
	}
	
	public function decorateSaveProperty($property) {
		//if ($property['isblank']) $blak = 
		
		$property['limit'] = 1;
		$property['width'] = (int)$property['width'];
		$property['height'] = (int)$property['height'];
		$property['intro'] = Pw::substrs($property['intro'],50);
		if ($property['cover'] == 2 ) {
			$result = $this->_uploadFile();
			if ($result  instanceof PwError ) return $result;
			$property['image'] = $result;
		}
		$property['html_tpl'] = '<for:><a href="{url}"><img src="{image}" ' ;
		$property['html_tpl'] .= $property['intro'] ? 'title="{intro}" ' : '';
		$property['html_tpl'] .= $property['width'] ? 'width="{width}" ' : '';
		$property['html_tpl'] .= $property['height'] ? 'height="{height}" ' : '';
		$property['html_tpl'] .= '></a></for>';
		return $property;
	}
	
	protected  function getData($field, $order, $limit, $offset) {
		$data[0]['image'] = $field['image'];
		$data[0]['width'] = $field['width'];
		$data[0]['height'] = $field['height'];
		$data[0]['url'] = $field['url'];
		$data[0]['intro'] = $field['intro'];
		return $data;
	}
	
	private function _uploadFile() {
 		Wind::import('SRV:upload.action.PwDesignImageUpload');
		Wind::import('SRV:upload.PwUpload');
		$bhv = new PwDesignImageUpload();
		$upload = new PwUpload($bhv);
		if (($result = $upload->check()) === true) $result = $upload->execute();
		if ($result !== true) return $result;
		$image = $bhv->getAttachInfo();
		if (!$image['filename']) return '';
		return Pw::getPath($image['path'] . $image['filename']);
 	}
	
}
?>