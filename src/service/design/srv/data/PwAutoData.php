<?php
Wind::import('SRV:design.srv.data.PwModuleData');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwAutoData.php 20578 2012-10-31 08:07:33Z gao.wanggao $ 
 * @package 
 */

class PwAutoData extends PwModuleData {
	
	private $_newPushIds = array();
	private $_newAutoIds = array();
	private $_reservData = array();
	/**
	 * 自动更新所有数据
	 */
	public function addAutoData() {
		$this->_getData();
		$this->setDesignData();
		$this->_addData();
	}
	
	private function _getData() {
		$limit =$this->getLimit();
		$this->_getPushData($limit);
		$limit = $limit - count($this->_newPushIds);
		if ($limit > 0){
			$param = $this->bo->getVoParam();
			$param['limit'] = $limit;
			$param['start'] = 0;
			$this->_getAutoData($param);
		}
	}
	
	
	private function _addData() {
		$delDataIds = $newOrderIds = array();
		$delImages = '';
		$ds = Wekit::load('design.PwDesignData');
		$pushDs = Wekit::load('design.PwDesignPush');
		Wind::import('SRV:design.dm.PwDesignDataDm');
		list($start, $end, $refresh) = $this->bo->refreshTime($this->time);
		foreach ($this->designData AS $k=>$v) {
			$k++;
			if (!$v) {
				$newOrderIds[] = $k;
				continue;
			}
			if ($v['from_type'] == PwDesignData::FROM_PUSH) {
				if (isset($this->_newPushIds[$v['from_id']])) {
					if ($v['data_type'] == PwDesignData::ISFIXED) {
						unset($this->_newPushIds[$v['from_id']]);
						continue;
					} 
					$data = $this->formatDesginData($v);
					$data['vieworder'] = $this->_newPushIds[$v['from_id']]['vieworder'];
					$data['from_type'] = $this->_newPushIds[$v['from_id']]['from_type'];
					if (!$this->_newPushIds[$v['from_id']]['vieworder'] && !$data['is_edited']) $data['data_type'] = PwDesignData::AUTO; 
					$this->_newPushIds[$v['from_id']] = $data;
					$delDataIds[] = $v['data_id'];
				   // $delImages .= $data['standard_image'];
					unset($v);
				}
			}
			
			if ($v['from_type'] == PwDesignData::FROM_AUTO) {
				if (isset($this->_newAutoIds[$v['from_id']])) {
					if ($v['data_type'] == PwDesignData::ISFIXED) {
						$ds->updateEndTime($v['data_id'], $refresh);
						unset($this->_newAutoIds[$v['from_id']]);
						continue;
					} 
					$data = $this->formatDesginData($v);
					$data['vieworder'] = $this->_newAutoIds[$v['from_id']]['vieworder'];
					$data['from_type'] = $this->_newAutoIds[$v['from_id']]['from_type'];
					$this->_newAutoIds[$v['from_id']] = $data;
					$delDataIds[] = $v['data_id'];
					$delImages .= $data['standard_image'];
					unset($v);	
				}
			}
			
			if ($v) {
				if ($v['data_type'] == PwDesignData::ISFIXED) {
					continue;
				}
				$delDataIds[] = $v['data_id'];
				if ($v['from_type'] != PwDesignData::FROM_PUSH){
					$extend = unserialize($v['extend_info']);
					$delImages .= $extend['standard_image'];
				}
			}
			
			$newOrderIds[] = $k;
		}
		$ds->batchDelete($delDataIds);
		Wekit::load('design.srv.PwDesignImage')->clearFiles($this->bo->moduleid, explode('|||', $delImages));
		//添加新显示数据
		$limit = count($delDataIds);
		$i = 1;
		foreach ($this->_newPushIds AS $key=>$newData) {
			if (!$newData['vieworder']) continue;
			if ($i > $limit) break;
			$_k = array_search($newData['vieworder'], $newOrderIds);
			if ($_k !== false) unset($newOrderIds[$_k]);
			$dm = new PwDesignDataDm();
			$dm->setDatatype(PwDesignData::AUTO)
				->setFromType(PwDesignData::FROM_AUTO)
	 			->setFromApp($newData['standard_fromapp'])
	 			->setFromid($newData['standard_fromid'])
	 			->setModuleid($this->bo->moduleid)
	 			->setStandard($newData['standard'])
	 			->setVieworder($newData['vieworder'])
	 			->setStarttime($this->time)
	 			->setEdited($newData['is_edited'])
	 			->setEndtime($refresh);
	 		if ($newData['standard_style']) {
	 			list($bold, $underline, $italic, $color) = $newData['standard_style'];
	 			$dm->setStyle($bold, $underline, $italic, $color);
	 		}
	 		if ($newData['from_type'] == 'push') {
	 			$dm->setFromType(PwDesignData::FROM_PUSH)
	 				->setStarttime($newData['start_time'])
	 				->setEndtime($newData['end_time']);
	 		}
	 		if ($newData['data_type']) $dm->setDatatype($newData['data_type']);
	 		if ($newData['vieworder']) $dm->setDatatype(PwDesignData::ISFIXED);
	 		$dm->setExtend($this->getExtend($newData));
	 		$resource = $ds->addData($dm);
	 		unset($this->_newPushIds[$key]);
	 		$i++;
		}
		
		foreach ($newOrderIds AS $order) {
			$isupdate = false;
			$newData = array_shift($this->_newPushIds);
			if (!$newData)  $newData = array_shift($this->_newAutoIds);
			if (!$newData) break;
			$newData['vieworder'] && $order = $newData['vieworder'];
			$dm = new PwDesignDataDm();
			$dm->setDatatype(PwDesignData::AUTO)
				->setFromType(PwDesignData::FROM_AUTO)
	 			->setFromApp($newData['standard_fromapp'])
	 			->setFromid($newData['standard_fromid'])
	 			->setModuleid($this->bo->moduleid)
	 			->setStandard($newData['standard'])
	 			->setVieworder($order)
	 			->setStarttime($this->time)
	 			->setEndtime($refresh)
	 			->setEdited($newData['is_edited']);
	 		if ($newData['standard_style']) {
	 			list($bold, $underline, $italic, $color) = $newData['standard_style'];
	 			$dm->setStyle($bold, $underline, $italic, $color);
	 		}
	 		if ($newData['from_type'] == 'push') {
	 			$dm->setFromType(PwDesignData::FROM_PUSH)
	 				->setStarttime($newData['start_time'])
	 				->setEndtime($newData['end_time']);
	 		}
	 		if ($newData['data_type']) $dm->setDatatype($newData['data_type']);
	 		if ($newData['vieworder']) $dm->setDatatype(PwDesignData::ISFIXED);
	 		
	 		$dm->setExtend($this->getExtend($newData));
	 		$resource = $ds->addData($dm);
		}
		
		//添加预定数据
		foreach ($this->_reservData AS $newData){
			$dm = new PwDesignDataDm();
			$dm->setDatatype(PwDesignData::AUTO)
	 			->setFromType(PwDesignData::FROM_PUSH)
	 			->setFromApp($newData['standard_fromapp'])
	 			->setFromid($newData['standard_fromid'])
	 			->setModuleid($this->bo->moduleid)
	 			->setStandard($newData['standard'])
	 			->setVieworder($newData['vieworder'])
	 			->setStarttime($newData['start_time'])
	 			->setEndtime($newData['end_time'])
				->setReservation(1)
				->setEdited(0);
			if ($newData['standard_style']) {
	 			list($bold, $underline, $italic, $color) = $newData['standard_style'];
	 			$dm->setStyle($bold, $underline, $italic, $color);
	 		}
	 		
	 		if ($newData['vieworder']) $dm->setDatatype(PwDesignData::ISFIXED);
	 		$dm->setExtend($this->getExtend($newData));
	 		$resource = $ds->addData($dm);
		}
	}
	
	/**
	 * 获取推送数据
	 * Enter description here ...
	 * @param int $limit
	 * @param int $start
	 * @param int $times 循环次数
	 */
	private function _getPushData($limit, $start = 0 , $times = 0) {
		$ds = Wekit::load('design.PwDesignPush');
		$vo = Wekit::load('design.srv.vo.PwDesignPushSo');
		$vo->setModuleid($this->bo->moduleid);
		$vo->setGtEndTime($this->time);
		$vo->setStatus(PwDesignPush::ISSHOW);
		$vo->orderbyPushid(false);
		$data = $ds->searchPush($vo, $limit, $start);
		$i = 0;
		$count = count($data);
		if ($count < 1) return true;
		foreach ($data AS $k=>$v) {
			if ($v['start_time'] > $this->time){
				$i++;
				$this->_reservData[] = $this->formatPushData($v);
				continue;
			}
			$data = $this->formatPushData($v);
			$this->_newPushIds[$data['standard_fromid']] = $data;
		}
		if ($count < $limit) return true;
		$start += $limit;
		$limit = $i;
		$times++;
		if ($i && $times < 100) $this->_getPushData($limit, $start, $times);
	}
	
	private function _getAutoData($param, $times = 0) {
		$limit = $param['limit'];
		$shieldids = $fromids = array();
		$model = $this->bo->getModel();
		if (!$model) return false;
		$cls = sprintf('PwDesign%sDataService', ucwords($model));
		$service = Wekit::load('design.srv.model.'.$model.'.'.$cls);
		$service->setModuleBo($this->bo);
		$data = $service->buildAutoData($param, $param['order'], $limit, $param['start']);
		$count = count($data);
		if ($count < 1) return true;
		$i = 0;
		foreach ($data AS $k=>$v) {
			$fromids[] = $v['standard_fromid'];
			
			//删除可能的重复项
			/*if (isset($_unique[$v['standard_fromid']])) {
				unset($data[$k]);
				$i++;
			} else {
				$_unique[$v['standard_fromid']] = $k;
			}*/
		}

		$shields = Wekit::load('design.PwDesignShield')->fetchByFromidsAndApp($fromids, $model);
		if ($shields) {
			foreach ($shields AS $v) {
				$shieldids[] = $v['from_id'];
			}
		}
		
		foreach ($data AS $k=>$v) {
			if (in_array($v['standard_fromid'], $shieldids) || $v['standard_title'] === ''){
				unset($data[$k]);
				$i++;
				continue;
			} else {
				$v['from_type'] = 'auto';
				$v['data_type'] = 1;
				$this->_newAutoIds[$v['standard_fromid']] = $v;
			}
		}
		if ($count < $limit) return true;
		$param['start'] += $limit;
		$param['limit'] = $i;
		$times++;
		if ($i && $times < 100) $this->_getAutoData($param, $times);
	}
}
?>