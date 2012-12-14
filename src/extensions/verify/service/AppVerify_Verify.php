<?php

/**
 * 实名认证Ds
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_Verify {
	
	const VERIFY_REALNAME = 1; // 真实姓名
	const VERIFY_AVATAR = 2; // 头像
	const VERIFY_EMAIL = 3; // 邮箱
	const VERIFY_ALIPAY = 4; // 支付宝
	const VERIFY_MOBILE = 5; // 手机
	
	const RIGHT_MESSAGE = 1; // 写私信
	const RIGHT_POSTTOPIC = 2; // 发表主题
	const RIGHT_POSTREPLY = 3; // 发表回复
	
	public function getVerifyType() {
		$array = array(
			self::VERIFY_REALNAME => '真实姓名',
			self::VERIFY_AVATAR => '头像',
			self::VERIFY_EMAIL => '电子邮箱',
			self::VERIFY_ALIPAY => '支付宝',
			self::VERIFY_MOBILE => '手机',
		);
		$activePhone = Wekit::C('register', 'active.phone');
		if (!$activePhone) {
			unset($array[self::VERIFY_MOBILE]);
		}
		return $array;
	}
	
	public function getVerifyTypeName() {
		$array = array(
			self::VERIFY_REALNAME => 'realname',
			self::VERIFY_AVATAR => 'avatar',
			self::VERIFY_EMAIL => 'email',
			self::VERIFY_ALIPAY => 'alipay',
			self::VERIFY_MOBILE => 'mobile',
		);
		$activePhone = Wekit::C('register', 'active.phone');
		if (!$activePhone) {
			unset($array[self::VERIFY_MOBILE]);
		}
		return $array;
	}
	
	public function getVerifyTypeByName($name) {
		$types = array_flip($this->getVerifyTypeName());
		return $types[$name];
	}
	
	public function getRightType() {
		return array(
			self::RIGHT_MESSAGE => '写私信',
			self::RIGHT_POSTTOPIC => '发表主题',
			self::RIGHT_POSTREPLY => '发表回复',
		);
	}
	
	public function getRightTypeName() {
		return array(
			self::RIGHT_MESSAGE => 'message',
			self::RIGHT_POSTTOPIC => 'postTopic',
			self::RIGHT_POSTREPLY => 'postReply',
		);
	}
	
	public function getOpenVerifyType() {
		$openType = Wekit::C('appVerify', 'verify.type');
		if (!$openType) return array();
		$array = array();
		foreach ($this->getVerifyType() as $k => $v) {
			Pw::getstatus($openType, $k) && $array[$k] = $v;
		}
		return $array;
	}
	
	public function getCheckVerifyType() {
		return array(
			self::VERIFY_REALNAME => '真实姓名',
		);
	}
	
	/**
	 * 获取一条数据
	 *
	 * @param int $uid
	 * @return array 
	 */
	public function getVerify($uid) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		return $this->_getDao()->get($uid);
	}
	
	/**
	 * 添加
	 *
	 * @param AppVerify_VerifyDm $dm
	 * @return bool 
	 */
	public function addVerify(AppVerify_VerifyDm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getDao()->add($dm->getData());
	}
	
	/**
	 * 编辑
	 *
	 * @param AppVerify_VerifyDm $dm
	 * @return bool 
	 */
	public function updateVerify($uid, AppVerify_VerifyDm $dm) {
		if (($result = $dm->beforeUpdate()) instanceof PwError) return $result;
		return $this->_getDao()->update($uid, $dm->getData(), $dm->getIncreaseData(), $dm->getBitData());
	}
	
	/**
	 * 添加替换
	 *
	 * @param AppVerify_VerifyDm $dm
	 * @return bool 
	 */
	public function replaceVerify(AppVerify_VerifyDm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getDao()->replace($dm->getData());
	}
	
	/**
	 * 删除一条
	 *
	 * @param int $id
	 * @return array 
	 */
	public function deleteVerify($uid) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		return $this->_getDao()->delete($uid);
	}	 

	/**
	 * 搜索统计
	 *
	 * @param PwWordSo $so
	 * @return int
	 */
	public function countSearchVerify(AppVerify_VerifySo $so) {
		return $this->_getDao()->countSearchVerify($so->getData());
	}

	/**
	 * 搜索数据
	 *
	 * @param PwWordSo $so
	 * @return array
	 */
	public function searchVerify(AppVerify_VerifySo $so, $limit = 20, $offset = 0) {
		return $this->_getDao()->searchVerify($so->getData(), $limit, $offset);
	}
	
	/**
	 * @return AppVerify_VerifyDao
	 */
	protected function _getDao() {
		return Wekit::loadDao('EXT:verify.service.dao.AppVerify_VerifyDao');
	}
}