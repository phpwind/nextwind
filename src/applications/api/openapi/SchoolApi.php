<?php
Wind::import('WINDID:api.open.OpenBaseApi');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: SchoolApi.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */
class SchoolApi extends OpenBaseApi {
	
	public function getSchool() {
		return $this->_getSchoolDs()->getSchool($this->getInput('id'));
	}
	
	public function fetchSchool(){
		return $this->_getSchoolDs()->fetchSchool(explode('_', $this->getInput('ids')));
	}
	
	public function getSchoolByAreaidAndTypeid() {
		return $this->_getSchoolDs()->getSchoolByAreaidAndTypeid($this->getInput('areaid'), $this->getInput('typeid'));
	}
	
	public function searchSchool($search, $limit, $start) {
		$start = $this->getInput('start');
		$limit = $this->getInput('limit');
		$search['name'] = $this->getInput('name');
		$search['typeid'] = $this->getInput('typeid');
		$search['areaid'] = $this->getInput('areaid');
		$search['firstchar'] = $this->getInput('firstchar');
		$search['endtime']= $this->getInput('endtime');
		return $this->getApi()->searchSchool($search, $limit, $start);
	}
	
	protected function getApi() {
		Wind::import('WINDID:api.local.WindidSchoolApi');
		return new WindidSchoolApi();
	}
	
	private function _getSchoolDs() {
		return Windid::load('school.WindidSchool');
	}
}
?>