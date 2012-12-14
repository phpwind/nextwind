<?php
/**
 * 用户注册基本流程注册
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwRegisterDoBase.php 15637 2012-08-09 09:20:55Z xiaoxia.xuxx $
 * @package src.service.user.srv.register.do
 */
abstract class PwRegisterDoBase {
	protected $bp = null;
	
	/**
	 * 构造函数
	 *
	 * @param PwRegisterService $bp
	 */
	public function __construct(PwRegisterService $bp) {
		$this->bp = $bp;
	}
	
	/**
	 * 注册必须实现的接口
	 *
	 * @param PwUserInfoDm $userDm
	 * @return true|PwError
	 */
	public function beforeRegister($userDm) {
		return true;
	}
	
	/** 
	 * 在注册结束之后执行
	 * 
	 * @param PwUserInfoDm $userDm
	 * @return true|PwError
	 */
	public function afterRegister($userDm) {
		return true;
	}
}