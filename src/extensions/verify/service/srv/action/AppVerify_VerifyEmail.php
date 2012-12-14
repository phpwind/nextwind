<?php
Wind::import('EXT:verify.service.srv.action.AppVerify_VerifyAction');

class AppVerify_VerifyEmail extends AppVerify_VerifyAction{
	
	public $unique = true;
	
	public function checkVerify($check) {
		
		return true;
	}
	
	public function buildDetail($check) {
		
		return $check;
	}
}