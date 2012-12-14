<?php

/**
 * 地区访问
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WebDataController.php 21452 2012-12-07 10:18:33Z gao.wanggao $ 
 * @package src.applications.bbs.controller
 */
class WebDataController extends PwBaseController {
	
	/**
	 * 地区库获取
	 */
	public function areaAction() {
		/* @var $areaService PwAreaService */
		$areaService = Wekit::load('area.srv.PwAreaService');
		$list = $areaService->getAreaTree();
		exit($list ? Pw::jsonEncode($list) : '');
	}
	
	/**
	 * 学校获取（typeid = 1:小学，2：中学，3：大学）
	 */
	public function schoolAction() {
		list($type, $areaid, $name, $first) = $this->getInput(array('typeid', 'areaid', 'name', 'first'));
		!$type && $type = 3;
		Wind::import('SRV:school.srv.vo.PwWindidSchoolSo');
		$schoolSo = new PwWindidSchoolSo();
		$schoolSo->setName($name)
			->setTypeid($type)
			->setFirstChar($first)
			->setAreaid($areaid);
		/* @var $schoolService PwSchoolService */
		$schoolService = Wekit::load('school.srv.PwSchoolService');
		$list = $schoolService->searchSchool($schoolSo, 1000);
		exit($list ? Pw::jsonEncode($list) : '');
	}
}