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
		return $this->_getSchoolDs()->getSchool($id);
	}
	
	public function fetchSchool($ids){
		return $this->_getSchoolDs()->fetchSchool($ids);
	}
	
	public function getSchoolByAreaidAndTypeid($areaid, $typeid) {
		return $this->_getSchoolDs()->getSchoolByAreaidAndTypeid($areaid, $typeid);
	}
	
	public function searchSchool($search, $limit =10, $start = 0) {
		if (!is_array($search)) return array();
		$array = array('name', 'typeid', 'areaid', 'firstchar');
		Wind::import('WINDID:service.school.vo.WindidSchoolSo');
		$vo = new WindidSchoolSo();
		foreach ($search AS $k=>$v) {
			if (!in_array($k, $array)) continue;
			$method = 'set'.ucfirst($k);
			$vo->$method($v);
		}
		return $this->_getSchoolDs()->searchSchool($vo, $limit, $start);
	}
	
	private function _getSchoolDs() {
		return Windid::load('school.WindidSchool');
	}
}
?>