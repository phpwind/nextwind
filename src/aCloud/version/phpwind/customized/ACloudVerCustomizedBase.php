<?php
! defined ( 'ACLOUD_PATH' ) && exit ( 'Forbidden' );
class ACloudVerCustomizedBase {
	
	public function __construct() {
		$daoObject = ACloudSysCoreCommon::getGlobal ( ACloudSysCoreDefine::ACLOUD_OBJECT_DAO );
		$daoObject->getDB ();
		list ( $currentUid ) = ACloudSysCoreS::gp ( 'current_uid' );
		ACloudSysCoreCommon::setGlobal ( 'customized_current_uid', $currentUid );
	}
	
	public function buildResponse($errorCode, $responseData = array()) {
		return array ($errorCode, $responseData );
	}
}