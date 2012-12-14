<?php
Wind::import('WINDID:library.WindidUtility');
Wind::import('WINDID:library.WindidError');
Wind::import('WINDID:service.client.bo.WindidClientBo');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: IndexController.php 21642 2012-12-12 04:56:39Z gao.wanggao $ 
 * @package 
 */
class IndexController extends PwBaseController {
	
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
		if ($time - $_time > 120) $this->output(WindidError::TIMEOUT);
		$charset = ($clent['charset'] == 1) ? 'utf8' : 'gbk';
		$baseUrl = Wind::getApp()->getRequest()->getBaseUrl(true) . '/';
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
	
	
	public function run() {
		$_api = $this->getInput('api', 'get');
		list($api, $method) =  explode('/', $_api);
		$array = array('user', 'config', 'message', 'avatar', 'area', 'school');
		if (!in_array($api, $array)) $this->output(WindidError::CLASS_ERROR);
		$class = 'Open'.ucfirst($api).'Api';
		Wind::import('WINDID:api.open.'.$class);
		$WindidApi = new $class();
		if(!method_exists($WindidApi, $method)) $this->output(WindidError::METHOD_ERROR);
		//$result = call_user_func_array(array($windidApi, $method), unserialize($data));
		$result = $WindidApi->$method();
		
		$this->output($result);
	}
	
	public function doavatarAction() {
		$uid = (int)$this->getInput('uid', 'get');
		Wind::import('WINDID:service.upload.action.WindidAvatarUpload');
		Wind::import('WINDID:service.upload.WindidUpload');
		$bhv = new WindidAvatarUpload($uid);
		
		$upload = new WindidUpload($bhv);
		if (($result = $upload->check()) === true) {
			$result = $upload->execute();
		}
		if ($result !== true) {
			$array = array(
				"isSuccess" => false, 
				"msg" => WindConvert::convert('上传失败', 'utf8', Wekit::app()->charset), 
				"erCode" => "000");
			//$this->showMessage($result->getMessage());
		} else {
			//用户上传头像之后的钩子
			PwSimpleHook::getInstance('update_avatar')->runDo($uid);
			$array = array(
				"isSuccess" => true, 
				"msg" => WindConvert::convert('上传成功', 'utf8', Wekit::app()->charset), 
				"erCode" => "000");
		}
		echo WindJson::encode($array, Windid::client()->clientCharser);
		exit;

	}

	
	protected function output($message = '') {
		header('Content-type: application/json');
		echo WindJson::encode($message, Windid::client()->clientCharser);
		exit();
	}
	
	private function _getAppDs() {
		return Windid::load('app.WindidApp');
	}

}
?>