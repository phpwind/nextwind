<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 回帖奖励后台设置相关服务
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardConfigService {

    protected $configKey = 'app_replyreward';
    protected $loginUser;

    public function __construct(){
        $this->loginUser = Wekit::getLoginUser();
    }

    public function getReplyRewardConfigByGid(){
        return $this->_getUserBo()->getPermission($this->configKey);
    }

    public function getReplyRewardConfigByGids($groupIds){
        if(!is_array($groupIds) || empty($groupIds)) return false;

        $config = $this->_getPwUserGroupsService()->getGroupCacheValue($groupIds);
        $result = array();

        foreach($config as $value){
            $gid = intval($value['gid']);
            $result[$gid] = $value['permission'][$this->configKey]['value'];
        }
        return $result;
    }   

    public function setReplyRewardAdminConfig($config){
        if(!is_array($config) || empty($config)) return false;
        Wind::import('SRV:usergroup.dm.PwUserPermissionDm');
        //var_dump($config);exit;
        foreach ($config as $gid => $value) {
            $gid = intval($gid);
            $value = intval($value);
            $dm = new PwUserPermissionDm($gid);
            $dm->setPermission($this->configKey, $value);
            $this->_getPwUserPermissionDs()->setPermission($dm);
        }
        return true;
    }


    /**
    *获取后台权限设置 - 具体权限 钩子调用 s_permissionConfig
    *
    */
    public function getPermissionConfig($config){
        if(!is_array($config) || empty($config)) return false;
        $config[$this->configKey] = array('radio', 'basic', '回帖奖励功能', ''); 
        return $config;
    }


    /**
    *
    *获取后台权限设置 - 权限类别  钩子调用 s_permissionCategoryConfig
    */
    public function getPermissionCategoryConfig($config){
        if(!is_array($config) || empty($config)) return false;
        $config['other']['sub'] = array(
            $this->configKey => array(
                'name' => '回帖奖励设置',
                'items' => array(
                    $this->configKey,
                )
            ),
            );
        return $config;
    }


    private function _getPwUserPermissionDs(){
        return Wekit::load('SRV:usergroup.PwUserPermission');
    }

    private function _getUserBo(){
        Wind::import('SRV:user.bo.PwUserBo');
        return PwUserBo::getInstance($this->loginUser->uid);
    }

    private function _getPwUserGroupsService(){
        return Wekit::load('SRV:usergroup.srv.PwUserGroupsService');
    }

}