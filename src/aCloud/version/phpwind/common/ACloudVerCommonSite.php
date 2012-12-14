<?php
! defined ( 'ACLOUD_PATH' ) && exit ( 'Forbidden' );

class ACloudVerCommonSite extends ACloudVerCommonBase {
	
	public function getTablePartitions($type) {
	
	}
	
	public function get(){
		$data = Wekit::config ( 'site' );
		$result = array();
		$result ['ifopen']   = $data['visit.state'];
		$result ['sitename'] = $data ['info.name'];
		$result ['siteurl'] = $data ['info.url'];
		$result ['charset'] = Wekit::app ()->charset;
		return $this->buildResponse(0,array('siteinfo' => $result));
	}
	
	public function getSiteVersion() {
		return $this->buildResponse ( 0, NEXT_VERSION );
	}

}