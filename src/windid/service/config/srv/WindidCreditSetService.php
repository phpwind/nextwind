<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 积分服务
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidCreditSetService.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package src.service.credit
 */
class WindidCreditSetService {
	

	/** 
	 * 设置用户积分
	 * 
	 * 算法步骤：
	 * 如果有新增的积分项（$newCredit）：（如果没有新增积分项，则直接跳转到步骤5）
	 * 1、先获取现有的数据表结构中的积分相关字段的列最大值 $maxCreditNum
	 * 2、根据最大的值获得积分区间 $creditRang(从1到$maxCreditNum)
	 * 3、根据现有传入的积分设置，和现有的最大区间得到差集，$freeDefaultCredit 该差集是目前区间内可用的积分项
	 * 4、扩展字段：（foreach）
	 * 4.1：如果在区间内没有可用的项，则下一个新增的积分项为目前最大积分项+1
	 * 4.2: 如果在区间内，则先采用区间内的值，从最小位开始补齐
	 * 4.2.1: 补齐的时候，判断如果该字段在表结构中已经存在，则不创建字段（1-8的字段不会被删除），否则创建字段
	 * 4.3： 将新的积分设置项和对应的新添加的字段关联，追加到creits中
	 * 5、将credits更新到设置
	 * 
	 * TODO 已知运营会有BUG：对于将原先使用的积分删除掉，用户对应的该积分的值没有做清空处理，
	 * 如果下次添加积分的时候重新启用了该字段，那么用户的该字段的积分将会在原先的基础上累加，注意：产品已知该情况。
	 * 
	 * @param array $credit 积分配置信息<array('1' => array('name'=>?,'unit'=>?,'descrip'=>?), '2' => ?, ...)>
	 * @param array $new 新增加的积分
	 * @return boolean
	 */
	public function setCredits($credits, $newCredit = array()) {
		if ($newCredit) {
			$struct = $this->_getDs()->getCreditStruct();
			$maxCreditNum = intval(str_replace('credit', '', max($struct)));
			$creditRang = range(1, $maxCreditNum, 1);
			$freeDefaultCredit = array_diff($creditRang, array_intersect($creditRang, array_keys($credits)));
			ksort($freeDefaultCredit);
			
			$next = 1;
			foreach ($newCredit as $id => $_item) {
				if (!$_item['name']) continue;
				$_new = 0;
				if (!$freeDefaultCredit) {
					$_new = $maxCreditNum + $next;
					if (!in_array('credit' . $_new, $struct)) {
						$this->_getDs()->alterAddCredit($_new);
					}
					$next ++;
				} else {
					$_new = array_shift($freeDefaultCredit);
					if (!in_array('credit' . $_new, $struct)) {
						$this->_getDs()->alterAddCredit($_new);
					}
				}
				$credits[$_new] = $_item;
			}
		}
		
		//更新windid的积分设置
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('credit');
		
		$config->set('credits', $credits)->flush();	

		return true;
	}

	/** 
	 * 删除积分
	 * 
	 * 涉及更新：
	 * 1、windid上的积分设置
	 * 2、本地的积分设置
	 * 3、用户积分的相关字段设置
	 * 3.1: 如果积分字段在8以内则只是清楚该列数据，如果积分字段在8以上，删除对应字段
	 *
	 * @param int $creditId 积分ID
	 * @return PwError|boolean
	 */
	public function deleteCredit($creditId) {
		if ($creditId < 0) return false;
		//更新windid的设置

		$credit = $this->_getConfigDs()->getValues('credit');
		$credits = $credit['credits'];
		unset($credits[$creditId]);
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('credit');
		$config->set('credits', $credits)->flush();	
		
		
		//删除字段
		if ($creditId < 9) {
			$this->_getDs()->clearCredit($creditId);
		} else {
			$this->_getDs()->alterDropCredit($creditId);
		}
		
		return true;
	}
	
	
	/**
	 * 获取DS
	 *
	 * @return PwUserDataExpand
	 */
	private function _getDs() {
		return Windid::load('user.WindidUser');
	}

	private function _getConfigDs() {
		return Windid::load('config.WindidConfig');
	}
}