<?php
Wind::import('WINDID:library.WindidUtility');
Wind::import('WINDID:library.WindidError');
Wind::import('WINDID:service.client.bo.WindidClientBo');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: OpenBaseController.php 22962 2013-01-04 05:11:30Z gao.wanggao $ 
 * @package 
 */
class OpenBaseController extends PwBaseController {
	
	public $app = array();
	
	public  function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		$charset = 'utf-8';
		$_windidkey = $this->getInput('windidkey', 'get');
		$_time = (int)$this->getInput('time', 'get');
		$_clientid = (int)$this->getInput('clientid', 'get');
		if (!$_time || !$_clientid) $this->output(WindidError::FAIL);
		$clent = $this->_getAppDs()->getApp($_clientid);
		if (!$clent) $this->output(WindidError::FAIL);
		if (WindidUtility::appKey($clent['id'], $_time, $clent['secretkey']) != $_windidkey)  $this->output(WindidError::FAIL);
		$time = Windid::getTime();
		if ($time - $_time > 1200) $this->output(WindidError::TIMEOUT);
		$charset = ($clent['charset'] == 1) ? 'utf8' : 'gbk';
		$baseUrl = Wind::getApp()->getRequest()->getBaseUrl(true);
		$config = array(
			'windid' => 'client',
			'serverUrl' =>  $baseUrl,
			'clientId' => $clent['id'],
			'clientKey' => $clent['secretkey'],
			'clientDb' => 'mysql',
			'clientCharser' => $charset,
		);
		WindidClientBo::getInstance($config);
		
	}
	
	protected function setDefaultTemplateName($handlerAdapter) {
		$this->setTemplate('');
	}
	
	
	public function run() {
		$this->output(0);
	}
	
	protected function output($message = '') {
		if (is_numeric($message)) {
			echo $message; 
			exit;
		} else {
			header('Content-type: application/json; charset='.Windid::client()->clientCharser);
			echo WindJson::encode($message, Windid::client()->clientCharser);
			exit();
		}
	}
	
	private function _getAppDs() {
		return Windid::load('app.WindidApp');
	}

}
?>