<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidAreaApi.php 21658 2012-12-12 07:00:12Z gao.wanggao $ 
 * @package 
 */
class WindidAreaApi {
	
	public function getArea($id) {
		$params = array(
			'id'=>$id,
		);
		return WindidApi::open('area/get', $params);
	}
	
	public function fetchArea($ids){
		$params = array(
			'ids'=>implode('_', $ids),
		);
		return WindidApi::open('user/fetch', $params);
	}
	
	public function getByParentid($parentid) {
		$params = array(
			'parentid'=>$parentid,
		);
		return WindidApi::open('area/getByParentid', $params);
	}
	
	public function getAll(){
		$params = array();
		return WindidApi::open('area/getAll', $params);
		if (!is_array($result)) return array();
		return $result;
	}
}
?>