<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRC:library.base.PwBaseDao');

/**
 * 回帖奖励Dao服务
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardDao extends PwBaseDao {
    protected $_table = 'app_replyreward';
    protected $_pk = 'tid';
    protected $_dataStruct = array('tid','credittype','creditnum','rewardtimes','repeattimes','chance','lefttimes');
    
    public function getRewardByTid($tid) {
        return $this->_get($tid);
    }
    
    public function fetchRewardByTids($tids) {
        return $this->_fetch($tids);
    }

    public function updateByTid($tid,$data){
        return $this->_update($tid,$data);
    }
    
    
    public function addReward($data){
        return $this->_add($data);
    }
    
    public function deleteByTid($tid){
        return $this->_delete($tid);
    }

    public function batchDeleteByTids($tids){
        return $this->_batchDelete($tids);
    }
        
}