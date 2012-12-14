<?php

/**
 * 实名认证service
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyService {

	/**
	 * 后台审核数据组装
	 *
	 * @param int $ifcheck
	 * @return array
	 */
	public function buildDetail($list) {
		$array = array();
		foreach ($list as $k => $v) {
			$class = $this->_getFactory($v['type']);
			$v = $class->buildDetail($v);
			$array[$k] = $v;
		}
		return $array;
	}

	/**
	 * 后台审核操作
	 * 
	 * @param int $ifcheck
	 * @param string $type
	 * @param int $limit
	 * @param int $start
	 * @return array
	 */
	public function checkVerify($ids){
		if (!is_array($ids) || !$ids) return false;
		$checks = $this->_getCheckDs()->fetchVerifyCheck($ids);
		foreach ($checks as $v) {
			$class = $this->_getFactory($v['type']);
			$res = $class->checkVerify($v);
			if ($res === true) {
				$this->updateVerifyInfo($v['uid'], $v['type']);
			}
		}
		return true;
	}

	/**
	 * 增加审核数据
	 * 
	 * @param $checkDm
	 * @return array
	 */
	public function addCheck(AppVerify_VerifyDm $checkDm) {
		$typeId = $checkDm->getField('type');
		$uid = $checkDm->getField('uid');
		$class = $this->_getFactory($typeId);
		if ($class->unique) {
			$check = $this->_getCheckDs()->getVerifyByUidAndType($uid, $typeId);
			if ($check) {
				return $this->_getCheckDs()->updateVerifyCheck($check['id'], $checkDm);
			}
		}
		return $this->_getCheckDs()->addVerifyCheck($checkDm);
	}

	/**
	 * 检测用户权限
	 * 
	 * @param $checkDm
	 * @return array
	 */
	public function checkVerifyRights($uid, $operator) {
		$rights = Wekit::C('appVerify', 'verify.rights');
		$verify = $this->_getDs()->getVerify($uid);
		$types = $this->_getDs()->getOpenVerifyType();
		$rightType = $this->_getDs()->getRightType();
		$right = $rights[$operator];
		if (!$right) return true;
		foreach ($types as $tk => $tv) {
			if (!Pw::getstatus($right, $tk)) continue;
			if (!Pw::getstatus($verify['type'], $tk)) {
				return new PwError('必须通过"'.$tv.'认证"才能'.$rightType[$operator]);
			}
		}
		return true;
	}
	
	public function updateVerifyInfo($uid, $type, $bool = true) {
		$result = $this->checkVerifyOpen($type);
		if (!$result) return true;
		Wind::import('EXT:verify.service.dm.AppVerify_VerifyDm');
		$dm = new AppVerify_VerifyDm($uid);
		$info = $this->_getDs()->getVerify($uid);
		if (!$info) {
			$dm->setUid($uid)
			->setType($this->buildVerifyBit(array($type => $bool)));
			return $this->_getDs()->addVerify($dm);
		}
		$dm->setBitType($type, $bool);
		return $this->_getDs()->updateVerify($uid, $dm);
	}
	
	public function checkVerifyOpen($type) {
		if (!Wekit::C('appVerify', 'verify.isopen')) return false;
		$openType = Wekit::C('appVerify', 'verify.type');
		if (!Pw::getstatus($openType, $type)) return false;
		return true;
	}
	
	public function buildVerifyBit($array) {
		if (!is_array($array)) return '';
		$str = '';
		foreach ($array as $bit => $v) {
			$str += $v ? (1 << $bit - 1) : 0;
		}
		return $str;
	}
	
	/**
	 * 创建找回密码的唯一标识
	 *
	 * @param string $uid 需要找回密码的用户名
	 * @param string $way 找回方式标识
	 * @param string $value 找回方式对应的值
	 * @return string
	 */
	public static function createIdentify($uid, $type, $passwd) {
		$code = Pw::encrypt($uid . '|' . $type . '|' . $passwd, Wekit::C('site', 'hash') . '___verify');
		return rawurlencode($code);
	}
	
	/**
	 * 解析找回密码的标识
	 *
	 * @param string $identify
	 * @return array array($username, $way, $value)
	 */
	public static function parserIdentify($identify) {
		return explode("|", Pw::decrypt(rawurldecode($identify), Wekit::C('site', 'hash') . '___verify'));
	}
	
	protected function _getFactory($typeId){
		if (!$typeId) return null;
		$types = $this->_getDs()->getVerifyTypeName();
		$type = $types[$typeId];
		if (!$type) return null;
		$type = strtolower($type);
		$className = sprintf('AppVerify_Verify%s', ucfirst($type));
		if (class_exists($className,false)) {
			return new $className();
		}
		$fliePath = 'EXT:verify.service.srv.action.'.$className;
		Wind::import($fliePath);
		return new $className();
	}

	/**
	 * @return AppVerify_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.AppVerify_Verify');
	}

	/**
	 * @return AppVerify_VerifyCheck
	 */
	protected function _getCheckDs() {
		return Wekit::load('EXT:verify.service.AppVerify_VerifyCheck');
	}
}
