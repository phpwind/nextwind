<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidSchoolApi.php 21658 2012-12-12 07:00:12Z gao.wanggao $ 
 * @package 
 */
class WindidSchoolApi {
	
	public function getSchool($id) {
		$params = array(
			'id'=>$id,
		);
		return WindidApi::open('school/get', $params);
	}
	
	public function fetchSchool($ids){
		$params = array(
			'ids'=>implode('_', $ids),
		);
		return WindidApi::open('school/fetch', $params);
	}
	
	public function getSchoolByAreaidAndTypeid($areaid, $typeid) {
		$params = array(
			'areaid'=>$areaid,
			'typeid'=>$typeid,
		);
		return WindidApi::open('school/getSchoolByAreaidAndTypeid', $params);
	}
	
	public function searchSchool($search, $limit = 10, $start = 0) {
		if (!is_array($search)) return array();
		$params = array(
			'limit'=>$limit,
			'start'=>$start,
		);
		$array = array('name', 'typeid', 'areaid', 'firstchar');
		foreach ($search AS $k=>$v) {
			if (!in_array($k, $array)) continue;
			$params[$k] = $v;
		}
		return WindidApi::open('school/search', $params);
	}
}
?>