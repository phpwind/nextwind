<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 社区银行日志DS基础服务
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */

 class App_ReplyReward_ReplyRewardRecord {
     
     
     /**
      *统计一个用户在一个帖子里的中奖次数
      */
     public function countRecordsByTidAndUid($tid, $uid){
        list($tid, $uid) = array(intval($tid), intval($uid));
        if ($tid < 1 || $uid < 1) return false;
        return $this->_getReplyRewardRecordDao()->countRecordsByTidAndUid($tid,$uid);
     }

     
     public function addRewardRecord(App_ReplyReward_ReplyRewardRecordDm $data){
        if (($result = $data->beforeAdd ()) !== true)
            return false;
        return $this->_getReplyRewardRecordDao()->addRewardRecord($data->getData());
        
     }
     
    public function getRewardRecordByTidAndPids($tid,$pids){
        $tid = intval($tid);
        if($tid < 1) return false;
        if(!is_array($pids) || empty($pids)) return false;
        $pids = array_map('intval', $pids);
        return $this->_getReplyRewardRecordDao()->getRewardRecordByTidAndPids($tid,$pids);
     }

      public function getRewardRecordByTid($tid){
        $tid = intval($tid);
        if ($tid < 1) return false;
        return $this->_getReplyRewardRecordDao()->getRewardRecordByTid($tid);
     }
     
     public function deleteByTid($tid){
         $tid = intval($tid);
         if($tid < 1) return false;
         $this->_getReplyRewardRecordDao()->deleteByTid($tid);
     }
     
     
    public function batchDeleteByTids($tids){
        if(!is_array($tids) || empty($tids)) return false;
        return $this->_getReplyRewardRecordDao()->batchDeleteByTids($tids);
    }

    protected function _getReplyRewardRecordDao() {
        return Wekit::loadDao('EXT:replyreward.service.dao.App_ReplyReward_ReplyRewardRecordDao');
    }
 }