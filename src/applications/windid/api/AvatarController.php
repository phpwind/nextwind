<?php
Wind::import('APPS:windid.api.OpenBaseController');
/**
 * 用户头像公共服务
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: AvatarController.php 22962 2013-01-04 05:11:30Z gao.wanggao $
 * @package windid.service.avatar
 */
class AvatarController  extends OpenBaseController{
	
	public $attachUrl = '';
	
	public function __construct() {
		$this->attachUrl = Windid::attachUrl();
	}
	
	/**
	 * 获取用户头像
	 * @param $uid
	 * @param $size big middle small
	 * @return string
	 */
	public function getAction() {
		$uid = $this->getInput('uid', 'get');
		$size = $this->getInput('size', 'get');
		!$size && $size = 'middle';
 		$file = $uid . (in_array($size, array('middle', 'small')) ? '_' . $size : '') . '.jpg';
		$result = $this->attachUrl . '/avatar/' . Windid::getUserDir($uid) . '/' . $file;
		$this->output($result);
	}
	
	
	/**
	 * 还原头像
	 *
	 * @param int $uid
	 * @param string $type 还原类型-一种默认头像face*,一种是禁止头像ban*
	 * @return boolean
	 */
	public function defaultAction() {
		$uid = $this->getInput('uid', 'post');
		$type = $this->getInput('type', 'post');
		!$type && $type = 'face';
		$srv = Windid::load('user.srv.WindidUserService');
		$result = $srv->defaultAvatar($uid, $type);
		$this->output($result);
	}
	
	/**
	 * 获取头像上传代码
	 *
	 * @param int $uid 用户uid
	 * @param int $getHtml 获取代码|配置
	 * @return string|array
	 */
	public function getFlashAction() {
		$uid = $this->getInput('uid', 'get');
		$getHtml = $this->getInput('getHtml', 'get');
		if($getHtml === null) $getHtml = 1;
		$client = Windid::client();
		$time = Windid::getTime();
		$key = WindidUtility::appKey($client->clientId,$time,$client->clientKey);
		$postUrl = "postAction=ra_postAction&redirectURL=/&requestURL=".urlencode($client->serverUrl . "/windid/index.php?m=api&c=avatar&a=doAvatar&uid=" . $uid.'&windidkey='.$key.'&time='.$time.'&clientid='.$client->clientId.'&type=flash').'&avatar=' .urlencode($this->getAvatar($uid, 'big').'?r='.rand(1,99999));
		$result = $getHtml ? '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="700" height="430" id="rainbow" align="middle">
							<param name="movie" value="'.Windid::resUrl().'swf/avatar/avatar.swf?'.rand(0,9999).'" />
							<param name="quality" value="high" />
							<param name="bgcolor" value="#ffffff" />
							<param name="play" value="true" />
							<param name="loop" value="true" />
							<param name="wmode" value="opaque" />
							<param name="scale" value="showall" />
							<param name="menu" value="true" />
							<param name="devicefont" value="false" />
							<param name="salign" value="" />
							<param name="allowScriptAccess" value="always" />
							<param name="FlashVars" value="'.$postUrl.'"/>
							<embed src="'.Windid::resUrl().'swf/avatar/avatar.swf?'.rand(0,9999).'" quality="high" bgcolor="#ffffff" width="700" height="430" name="mycamera" align="middle" allowScriptAccess="always" allowFullScreen="false" scale="exactfit"  wmode="transparent" FlashVars="'.$postUrl.'" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
						</object>'
		               : array(
		                    'width' => '500',
		                    'height' => '405',
		                    'id' =>'uploadAvatar',
		                    'name' => 'uploadAvatar',
		                    'src' => Windid::resUrl().'swf/avatar/avatar.swf',
		                    'wmode' => 'transparent',
		                    'postUrl'=> $client->serverUrl . "/windid/index.php?m=api&c=avatar&a=doAvatar&uid=" . $uid.'&windidkey='.$key.'&time='.$time.'&clientid='.$client->clientId.'&type=normal',
		               		'token' => $key,
		                );
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
		if ($result instanceof WindidError) {
			$this->output($result->getCode());
		} else {
			$this->_getNotifyClient()->send('uploadAvatar', $uid, true);
			$this->output(1);
		}
	}
	
	private function _getNotifyClient() {
		return Windid::load('notify.srv.WindidNotifyClient');
	}
	
}