<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 后台菜单添加
 *
 * @author fengxiao <xiao.fengx@alibaba-inc.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_EncryptPosts_ConfigDo {
	

	private $configKey = 'app_encryptposts';
    protected $loginUser;

    public function __construct(){
        $this->loginUser = Wekit::getLoginUser();
    }

	public function getConfigByGids($gids){
        if(!is_array($gids) || empty($gids)) return false;
        $gids = array_map('intval',$gids);

        $config = $this->_getPwUserGroupsService()->getGroupCacheValue($gids);
        $result = array();

        foreach($config as $value){
            $gid = intval($value['gid']);
            $result[$gid] = $value['permission'][$this->configKey]['value'];
        }
        return $result;
	}

	public function setConfig($config){
        if(!is_array($config) || empty($config)) return false;
        Wind::import('SRV:usergroup.dm.PwUserPermissionDm');
        foreach ($config as $gid => $value) {
            $gid = intval($gid);
            $value = intval($value);
            $dm = new PwUserPermissionDm($gid);
            $dm->setPermission($this->configKey, $value);
            $this->_getPwUserPermissionDs()->setPermission($dm);
        }
        return true;
	}

    public function getConfigByGid(){
        return $this->_getUserBo()->getPermission($this->configKey);
    }


    /**
    *获取后台权限设置 - 具体权限 钩子调用 s_permissionConfig
    *
    */
    public function getPermissionConfig($config){
        if(!is_array($config) || empty($config)) return false;
        $config[$this->configKey] = array('radio', 'basic', '帖子加密功能', ''); 
        return $config;
    }

    private function _getUserBo(){
        Wind::import('SRV:user.bo.PwUserBo');
        return PwUserBo::getInstance($this->loginUser->uid);
    }

    private function _getPwUserPermissionDs(){
        return Wekit::load('SRV:usergroup.PwUserPermission');
    }

    private function _getPwUserGroupsService(){
        return Wekit::load('SRV:usergroup.srv.PwUserGroupsService');
    }


}

?>