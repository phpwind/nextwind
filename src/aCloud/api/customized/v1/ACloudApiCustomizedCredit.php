<?php
! defined ( 'ACLOUD_PATH' ) && exit ( 'Forbidden' );
require_once Wind::getRealPath ( "ACLOUD_VER:customized.ACloudVerCustomizedFactory" );
class AcloudApiCustomizedCredit {

	public function fetchCreditType(){
		return $this->getVersionCustomizedCredit()->fetchCreditType();
	}

	public function setCredit($uid,$ctype,$point){
		return $this->getVersionCustomizedCredit()->setCredit($uid,$ctype,$point);
	}

	private function getVersionCustomizedCredit() {
		return ACloudVerCustomizedFactory::getInstance ()->getVersionCustomizedCredit ();
	}
}