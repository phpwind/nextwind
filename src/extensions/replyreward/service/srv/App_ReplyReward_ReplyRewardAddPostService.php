<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('SRV:forum.srv.post.do.PwPostDoBase');


/**
 * 回帖奖励--帖子阅读页
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardAddPostService extends PwPostDoBase{

    private $created_userid;
    private $fid;
    private $forum_name;
    private $tid;
    private $uid;
    private $username;
    private $loginUser;

    public function __construct(PwPost $pwpost){
        $info = $pwpost->action->getInfo();
        $this->loginUser = $pwpost->user;
        $this->fid = intval($info['fid']);
        $this->uid = intval($pwpost->user->uid);
        $this->created_userid = intval($info['created_userid']);
        $this->tid = intval($info['tid']);
        $forumInfo = $pwpost->forum->foruminfo;
        $this->forum_name = $forumInfo['name'];
        $this->username = intval($pwpost->user->username);
    }

    /**
     *
     * 钩子 回复帖子
     */
    public function addPost($pid,$tid){
        return $this->rewardReplyUser($pid,$tid);
    }

    /**
     * 回帖奖励 ReplyRewardAddPostService 调用
     * 
     * @param type $tid
     * @param type $pid
     * @return boolean
     */
    public function rewardReplyUser($pid,$tid){
        list($tid, $pid) = array(intval($tid), intval($pid));
        if(! $tid || ! $pid) return false;

        if($this->created_userid == $this->uid) return false;

        $rewardInfo = $this->getReplyRewardBo()->getRewardInfo();
        
        if (!$this->_checkRewardCondition($rewardInfo, $tid) || !$this->_checkIfReward($rewardInfo['chance'])) return false;
        return $this->_rewardUser($tid, $pid, $rewardInfo);
    }

    /**
     * 插入新的中奖信息到数据库中
     * @param $data
     */
    private function _addRewardRecord($data) {
        if (!is_array($data) || !count($data))
            return false;
        $dm = $this->_getReplyRewardRecordDm();
        $dm->setTid($data['tid'])
            ->setPid($data['pid'])
            ->setUid($data['uid'])
            ->setCreditType($data['credittype'])
            ->setCreditNum($data['creditnum'])
            ->setRewardTime($data['rewardtime']);
        return $this->_getReplyRewardRecordDs()->addRewardRecord($dm);
    }

    private function _updateByTid($tid,$lefttimes){
        list($tid,$lefttimes) = array(intval($tid),intval($lefttimes));
        if($tid < 0 || $lefttimes < 0 ) return false;
        $dm = $this->_getReplyRewardDm();
        $dm->tid = $tid;
        $dm->setLeftTimes($lefttimes);
        return $this->_getReplyRewardDs()->updateByTid($dm);
    }

    /**
     * 具体奖励操作
     * @param $uid
     * @param $tid
     * @param $pid
     * @param $rewardInfo
     */
    private function _rewardUser($tid, $pid,$rewardInfo) {
        //global $credit;
        $record = array(
            'tid' => intval($tid),
            'pid' => intval($pid),
            'uid' => intval($this->uid),
            'credittype' => $rewardInfo['credittype'],
            'creditnum' => $rewardInfo['creditnum'],
            'rewardtime' => time()
        );
        $this->_addRewardRecord($record);
        $lefttimes = ($rewardInfo['lefttimes'] - 1 >= 0) ? $rewardInfo['lefttimes'] - 1 : 0;
        
        $this->_updateByTid($tid,$lefttimes);
        $this->_getCreditBoInstance()->set($this->uid, $rewardInfo['credittype'], $rewardInfo['creditnum']);

        //积分日志
        $this->_getCreditBoInstance()->addLog($this->_getReplyRewardLogService()->getConfigKey(),array($rewardInfo['credittype']=>$rewardInfo['creditnum']),$this->loginUser,array(
            'username' => $this->username,
            'forumname' => $this->forum_name,
            'action' => '回复',
            'type' => '帖子',
            'cname' => $this->_getCreditBoInstance()->cUnit[$rewardInfo['credittype']],
            'affect' => $rewardInfo['creditnum'],
            ));
    }


    /**
     * 中奖概率
     * @param $chance
     */
    private function _checkIfReward($chance) {
        return rand(1, 10) <= ($chance / 10);
    }

    /**
     * 检查条件
     * @param $rewardInfo
     * @param $tid
     */
    private function _checkRewardCondition($rewardInfo, $tid) {
        if (!$rewardInfo || !$rewardInfo['rewardtimes'] || $rewardInfo['lefttimes'] < 1) return false;
        $rewardRecords = $this->_getReplyRewardRecordDs()->countRecordsByTidAndUid($tid, $this->uid);
        if ($rewardInfo['repeattimes'] && $rewardRecords >= $rewardInfo['repeattimes']) return false;
        return true;
    }

    public function getReplyRewardBo() {
        static $_instance = null;
        
        if ($_instance == null) {
            Wind::import('EXT:replyreward.service.srv.bo.App_ReplyReward_Bo');
            $_instance = new App_ReplyReward_Bo($this->tid);
        }
        return $_instance;
    }

    private function _getReplyRewardRecordDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyRewardRecord');
    }

    private function _getReplyRewardLogService(){
        return Wekit::load('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardLogService');
    }

    private function _getReplyRewardRecordService(){
        return Wekit::load('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardRecordService');
    }

    private function _getReplyRewardRecordDm(){
        return Wekit::load('EXT:replyreward.service.dm.App_ReplyReward_ReplyRewardRecordDm');
    }

    private function _getReplyRewardDm(){
        return Wekit::load('EXT:replyreward.service.dm.App_ReplyReward_ReplyRewardDm');
    }

    private function _getReplyRewardDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyReward');
    }

    private function _getCreditBoInstance(){
        Wind::import('SRV:credit.bo.PwCreditBo');
        return PwCreditBo::getInstance();
    }
    
}