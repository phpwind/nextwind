<?php
/**
 *
 * @author jinling.su<emily100813@gmail.com> 2012-5-28
 * @link http://www.phpwind.com
 * @copyright Copyright &copy; 2003-2010 phpwind.com
 * @version $Id: IndexController.php 21696 2012-12-12 08:15:17Z long.shi $
 */
class IndexController extends PwBaseController {
	private $perpage = 10;
	private $orderBy = array('time' => 'created_time');

	public function run() {
		$page = intval($this->getInput('page'));
		$page < 1 && $page = 1;
		list($start, $num) = Pw::page2limit($page, $this->perpage);
		$orderBy = $this->getInput('orderby', 'get');
		if (!$orderBy || !isset($this->orderBy[$orderBy])) {
			$orderBy = key($this->orderBy);
		}
		$count = $this->_appDs()->countByStatus(1);
		$apps = $this->_appDs()->fetchListByStatus($num, $start, 1, $this->orderBy[$orderBy]);
		$return = array();
		foreach ($apps as $k => $v) {
			$return[] = array(
				'app_id' => $k, 
				'name' => $v['name'], 
				'logo' => $v['logo'], 
				'alias' => $v['alias'], 
				'desc' => $v['description'] ? $v['description'] : '这家伙很懒', 
				'url' => $v['status'] & 8 ? WindUrlHelper::createUrl('appcenter/apps/run?appid=' . $v['app_id']) : WindUrlHelper::createUrl('app/index/run?app=' . $v['alias']));
		}
		$this->setOutput(
			array(
				'apps' => $return, 
				'count' => $count, 
				'perpage' => $this->perpage, 
				'page' => $page,
				'orderby' => $orderBy
				));
		$this->setTemplate('app_index_run');
		// seo设置
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		PwSeoBo::setCustomSeo($lang->getMessage('SEO:appcenter.appindex.run.title'), '', '');
	}

	/**
	 *
	 * @return PwApplication
	 */
	private function _appDs() {
		return Wekit::load('APPS:appcenter.service.PwApplication');
	}
}