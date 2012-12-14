<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.srv.post.do.PwPostDoBase');
Wind::import('SRC:library.Pw');
Wind::import('SRC:library.engine.error.PwError');

/**
 * 回复奖励相关服务类 （主题相关）
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardService extends PwPostDoBase{
    
    protected $loginUser;

    protected $rewardData;    
    protected $tid;
    protected $fid;
    protected $forum_name;
	protected $action;

    public function __construct(PwPost $pwpost,$tid = null) {
        $this->loginUser = $pwpost->user;

        $this->tid = $tid ? intval($tid) : null;
        $this->fid = intval($pwpost->forum->fid);
        $forumInfo = $pwpost->forum->foruminfo;
        $this->forum_name = $forumInfo['name'];
        $this->action = $this->tid ? 'modify' : 'add';
    }


    /**
     * 钩子方法 帖子发布展示
     */
    public function createHtmlRightContent() {
        if($this->action == 'add' && $this->_checkPrivilege()){
            PwHook::template('displayPostReplyRewardHtml', 'EXT:replyreward.template.post_replyreward', true, $this);
        }elseif($this->action == 'modify' && $this->getReplyRewardBo()->getRewardInfo()){
            PwHook::template('displayModifyReplyRewardHtml', 'EXT:replyreward.template.modify_replyreward', true, $this);
        }
    }

    /**
     * 钩子---帖子发布前，数据检查
     *
     * @param PwPostDm $postDm
     * @return true|PwError
     */
    public function check($postDm){        
        
        $replyReward = $this->_getRewardInput();

        if($replyReward['replyreward'] == 1){
            if(!$this->_checkPrivilege()) return new PwError('没有权限');

            if(intval($replyReward['credittype']) < 1 || abs(intval($replyReward['creditnum'])) < 1 || abs(intval($replyReward['rewardtimes'])) < 1|| intval($replyReward['repeattimes']) < 1 || intval($replyReward['chance']) < 1){
                return new PwError('已开启回帖奖励，必填项不能为空');
            }
            
            if(!$this->_checkCreditSet()){
                return new PwError('回帖奖励设置的积分超限，请重新设置'); 
            }
                
        }

        return true;
    }


    /**
    * 发表帖子
    */
    public function addThread($tid){
		$data = $this->_getRewardInput();
		if(intval($data['replyreward']) != 1) return false;
        $tid = intval($tid);
        if($tid < 1) return false;

        $data['tid'] = $tid;
        $data['fid'] = $this->fid;
        $data['creditnum'] = abs(intval($data['creditnum']));
        $data['rewardtimes'] = abs(intval($data['rewardtimes']));
        return $this->_addNewReward($data);
    }


    /**
     * 获取积分类型
     * 
     * @return type
     */
    public function getRewardCreditType(){
        foreach($this->_getCreditBoInstance()->cType as $key => $value){
            $replyrewardcredit .= "<option value=\"$key\">" . $value . "</option>";
        }
        echo $replyrewardcredit;
    }
    
    public function getUserAllCredits(){
        return $this->getReplyRewardBo()->getUserAllCredits();
    }

    /**
     * 判断权限
     * 
     */
    private function _checkPrivilege(){
        //后台权限
        $groupId = intval($this->loginUser->gid);
        if($this->_getReplyRewardConfigService()->getReplyRewardConfigByGid()){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 根据tid获取设置项信息
     * @param $tid
     */
    public function getRewardByTid($tid) {
        $tid = intval($tid);
        if ($tid < 1) return false;
        return $this->_getReplyRewardDs()->getRewardByTid($tid);
    }
    

    public function setRewardInput($data){
        $this->rewardData = $data;
    }


    /**
     *检查设置的奖励积分是否不足
     */
    private function _checkCreditSet(){
        $data = $this->_getRewardInput();
        $userCreditInfo = intval($this->getReplyRewardBo()->getCreditNum($data['credittype']));
        $setCreditInfo = intval(abs($data['creditnum']) * abs($data['rewardtimes']));

        if($userCreditInfo < $setCreditInfo)
            return false;
        return true;
        
    }


    private function _getRewardInput(){
        return $this->rewardData;
    }


    /**
     * 增加一个新的回帖奖励
     * @param $data
     */
    private function _addNewReward($data) {
        $uid = $this->loginUser->uid;
        if ($uid < 1 || !is_array($data) || !count($data)) return false; 
        $fid = intval($data['fid']);
        if($fid < 1) return false;
        $this->_addReward($data);
        $points = ceil($data['creditnum'] * $data['rewardtimes']);
        $this->_getCreditBoInstance()->addLog($this->_getReplyRewardLogService()->getConfigKey(),array($data['credittype']=>-$points),$this->loginUser,array(
            'username' => $this->loginUser->info->username,
            'forumname' => $this->forum_name,
            'action' => '发表',
            'type' => '主题',
            'cname' => $this->_getCreditBoInstance()->cUnit[$data['credittype']],
            'affect' => -$points,
            ));
        
        return $this->_getCreditBoInstance()->set($uid, $data['credittype'], -$points);
    }
    


    /**
     * 插入新的设置信息到数据库中
     * @param $data
     */
    private function _addReward($data) {
        if (!is_array($data) || empty($data)) return false;
        $dm = $this->_getReplyRewardDm();
        $dm->setTid($data['tid'])
            ->setCreditType($data['credittype'])
            ->setCreditNum($data['creditnum'])
            ->setRewardTimes($data['rewardtimes'])
            ->setRepeatTimes($data['repeattimes'])
            ->setChance($data['chance'])
            ->setLeftTimes($data['rewardtimes']);
        return $this->_getReplyRewardDs()->addReward($dm);
    }

   
    public function getReplyRewardBo() {
        static $_instance = null;
        
        if ($_instance == null) {
            Wind::import('EXT:replyreward.service.srv.bo.App_ReplyReward_Bo');
            $_instance = new App_ReplyReward_Bo($this->tid);
        }
        return $_instance;
    }

    private function _getReplyRewardDm(){
        return Wekit::load('EXT:replyreward.service.dm.App_ReplyReward_ReplyRewardDm');
    }
    
    private function _getReplyRewardLogService(){
        return Wekit::load('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardLogService');
    }


    private function _getCreditBoInstance(){
        Wind::import('SRV:credit.bo.PwCreditBo');
        return PwCreditBo::getInstance();
    }
    
    private function _getReplyRewardDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyReward');
    }
    
    private function _getReplyRewardConfigService(){
        return Wekit::load('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardConfigService');
    }

}