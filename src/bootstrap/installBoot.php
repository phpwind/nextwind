<?php

Wind::import('SRC:bootstrap.bootstrap');

class installBoot extends bootstrap {

	public function getConfig() {
	}
	
	public function getTime() {
		return time();
	}
}