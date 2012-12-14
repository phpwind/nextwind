<?php

abstract class AppVerify_VerifyAction{
	
	public $unique = true;
	
	abstract function checkVerify($check);
	
	abstract function buildDetail($check);
}