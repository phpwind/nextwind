<?php
Wind::import('APPS:windid.api.OpenBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: SchoolController.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */
class SchoolController extends OpenBaseController {
	
	public function getAction() {
		$result = $this->_getSchoolDs()->getSchool($this->getInput('id', 'get'));
		$this->output($result);
	}
	
	public function fetchAction(){
		$result = $this->_getSchoolDs()->fetchSchool(explode('_', $this->getInput('ids', 'get')));
		$this->output($result);
	}
	
	public function getSchoolByAreaidAndTypeidAction() {
		$result = $this->_getSchoolDs()->getSchoolByAreaidAndTypeid($this->getInput('areaid', 'get'), $this->getInput('typeid', 'get'));
		$this->output($result);
	}
	
	public function searchAction($search, $limit, $start) {
		$start = (int)$this->getInput('start', 'get');
		$limit = (int)$this->getInput('limit', 'get');
		$search['name'] = $this->getInput('name', 'get');
		$search['typeid'] = $this->getInput('typeid', 'get');
		$search['areaid'] = $this->getInput('areaid', 'get');
		$search['firstchar'] = $this->getInput('firstchar', 'get');
		$search['endtime']= $this->getInput('endtime', 'get');
		!$limit && $limit = 10;
		$result = $this->getApi()->searchSchool($search, $limit, $start);
		$this->output($result);
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