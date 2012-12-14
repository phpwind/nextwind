<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRC:library.base.PwBaseDao');

/**
 * 社区银行日志Dao服务
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */

class App_ReplyReward_ReplyRewardRecordDao extends PwBaseDao {
    protected $_table = 'app_replyreward_record';
    protected $_pk = 'id';
    protected $_dataStruct = array('tid', 'pid', 'uid', 'credittype', 'creditnum', 'rewardtime');

    
    
    public function countRecordsByTidAndUid($tid, $uid){
        $sql = $this->_bindSql("SELECT count(*) as counts FROM %s WHERE tid=? AND uid=?",$this->getTable());
        $smt = $this->getConnection()->createStatement($sql);
        return $smt->getValue(array($tid,$uid));
    }
    
    
    public function addRewardRecord($data){
        return $this->_add($data);
    }
    
    public function getRewardRecordByTidAndPids($tid,$pids){
        $sql = $this->_bindSql("SELECT * FROM %s WHERE tid=? AND pid IN %s",$this->getTable(),$this->sqlImplode($pids));
        $smt = $this->getConnection()->createStatement($sql);
        return $smt->queryAll(array($tid));
    }

    public function getRewardRecordByTid($tid){
        $sql = $this->_bindSql("SELECT * FROM %s WHERE tid=?",$this->getTable());
        $smt = $this->getConnection()->createStatement($sql);
        return $smt->queryAll(array($tid));
    }
    
    public function deleteByTid($tid){
        $sql = $this->_bindSql("DELETE FROM %s WHERE tid=?",$this->getTable());
        $smt = $this->getConnection()->createStatement($sql);
        return $smt->execute(array($tid));
    }

    public function batchDeleteByTids($tids){
        $sql = $this->_bindSql('DELETE FROM %s WHERE tid IN %s',$this->getTable(),$this->sqlImplode($tids));
        return $this->getConnection()->execute($sql);
    }
    
}