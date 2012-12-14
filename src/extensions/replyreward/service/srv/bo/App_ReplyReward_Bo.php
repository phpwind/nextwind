<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 单个用户的业务对象
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_Bo {

	private $info = array();
	private $tid;
	private $loginUser;
	
	/** 
	 * 构造函数信息
	 *
	 */
	public function __construct($tid) {
		$this->loginUser = Wekit::getLoginUser();
		$this->tid = intval($tid);
		$this->info = $this->tid ? $this->_getReplyRewardDs()->getRewardByTid($tid) : array();
	}
	
	/**
	 * 检测信息是否初始化
	 * 
	 * @return boolean
	 */
	public function isInit() {
		return $this->info ? true : false;
	}


    public function getCreditPoolNum(){
        return $this->info['lefttimes'] * $this->info['creditnum'];   
    }


    public function getUserAllCredits(){
        $userAllCredits = array();
        foreach($this->_getCreditBoInstance()->cType as $key => $value){
            $creditInfo = $this->getCreditInfo($key);
            $userAllCredits['c' . $key] = $this->getCreditInfo($key);
        }
        return $userAllCredits;
    }

    /**
     * 获取用户指定积分的名称，数量，单位
     * 
     * 返回如：array('铜币',4,'枚')
     * 
     * @param type $type
     * @return type
     * 
     */
    public function getCreditInfo($type){
        $type = intval($type);
        if($type < 1) return false;
        $creditBo = $this->_getCreditBoInstance();
        $creditInfo['name'] = $creditBo->cType[$type];
        $creditInfo['num'] = $this->loginUser->info['credit'.$type];
        $creditInfo['unit'] = $creditBo->cUnit[$type];
        return $creditInfo;
    }
    

    public function getCreditName($type){
        $creditInfo = $this->getCreditInfo($type);
        return $creditInfo['name'];
    }

    public function getCreditNum($type){
        $creditInfo = $this->getCreditInfo($type);
        return $creditInfo['num'];
    } 

    public function getCreditUnit($type){
        $creditInfo = $this->getCreditInfo($type);
        return $creditInfo['unit'];
    }


	public function getRewardInfo(){
		return $this->info;
	}


    private function _getCreditBoInstance(){
        Wind::import('SRV:credit.bo.PwCreditBo');
        return PwCreditBo::getInstance();
    }

    private function _getReplyRewardDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyReward');
    }
}