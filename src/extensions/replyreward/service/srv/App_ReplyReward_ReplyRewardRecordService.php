<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase');

/**
 * 回复奖励相关服务类 （回帖中奖相关）
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardRecordService extends PwThreadDisplayDoBase{

    protected $tid;
    protected $pid;
    protected $loginUser;
    protected $threadInfo;
    protected $floorInfo;

    protected $pids = array();

    protected $isIn = false;
    protected $isReplyReward = true;

    public function __construct() {
        $this->loginUser = Wekit::getLoginUser();
    }

    /**
     * 钩子执行 收集pid 加工帖子数据
     */
    public function bulidRead($read){
        if(intval($read['pid']) == 0) return $read;
        $this->pids[] = intval($read['pid']);
        return $read;
    }

    /**
     *帖子阅读页 - 钩子方法
     *
     */
    public function createHtmlBeforeContent($read) {
        if(!is_array($read) || empty($read)) return false;
        !$this->tid && $this->tid = intval($read['tid']);
        $this->pid = intval($read['pid']);

        //快速回复帖子
        if(!$this->pids && $this->pid != 0) $this->pids[] = $this->pid;
        //first，需要组装数据
        if($this->isIn === false){

            $rewardInfo = $this->getReplyRewardBo()->getRewardInfo();
            if(!$rewardInfo) $this->isReplyReward = false;

            $recordInfo = $this->pids ? $this->_getReplyRewardRecordDs()->getRewardRecordByTidAndPids($this->tid,$this->pids) : array();
            $creditInfo = $this->getReplyRewardBo()->getCreditInfo($rewardInfo['credittype']);

            $this->threadInfo = array(
                'rewardInfo' => $rewardInfo['creditnum'] . $creditInfo['unit'] .  $creditInfo['name'],
                'creditnum' => $rewardInfo['creditnum'],
                'creditunit' => $creditInfo['unit'],
                'creditname' => $creditInfo['name'],
                'repeattimes' => $rewardInfo['repeattimes'],
                'chance' => $rewardInfo['chance'],
                'pool' => $rewardInfo['lefttimes'] * $rewardInfo['creditnum'],
                );

            foreach($recordInfo as $val){
                $this->floorInfo[$val['pid']] = $rewardInfo['creditnum'];
            }

            $this->isIn = true;
        }

        if($this->pid > 0 && !$this->floorInfo[$this->pid])
            return false;

        if($this->tid > 0 && $this->pid == 0 && $this->isReplyReward === false)
            return false;

        PwHook::template('displayReadBeforeContentHtml', 'EXT:replyreward.template.read_replyreward', true, $this);
    }


    public function getThreadInfo(){
        return $this->threadInfo;
    }

    public function getFloorInfo(){
        $info = $this->floorInfo;
        return $info[$this->pid];
    }


    public function getPid(){
        return $this->pid;
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
    
    private function _getReplyRewardDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyReward');
    }
    
    private function _getCreditBoInstance(){
        Wind::import('SRV:credit.bo.PwCreditBo');
        return PwCreditBo::getInstance();
    }

    
}