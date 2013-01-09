<?php
! defined ( 'ACLOUD_PATH' ) && exit ( 'Forbidden' );


class ACloudVerCustomizedCredit extends ACloudVerCustomizedBase {

	public function fetchCreditType(){
		Wind::import('SRV:credit.bo.PwCreditBo');
		return $this->buildResponse(0,PwCreditBo::getInstance()->cType);
	}

	public function setCredit($uid,$ctype,$point){
		Wind::import('SRV:credit.dm.PwCreditDm');
		$dm = new PwCreditDm($uid);
		$dm -> addCredit($ctype,$point);
		$result = $this->_loadPwUserDS()->updateCredit($dm);
		if(!$result){
			return $this->buildResponse(0,$point);
		}
		return $this->buildResponse(-1,'设置积分失败');

	}

	private function _loadPwUserDS(){
		return Wekit::load('SRV:user.PwUser');
	}

}