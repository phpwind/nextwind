<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('SRV:forum.srv.post.do.PwPostDoBase');
/**
 * 后台菜单添加
 *
 * @author fengxiao <xiao.fengx@alibaba-inc.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_EncryptPosts_EncryptPostsService extends PwPostDoBase{

	private $inputData;
    private $tid;
    private $action;
    public $ifCheck = false;

    public function __construct(PwPost $pwpost,$tid = null) {
        $this->loginUser = $pwpost->user;
        $this->tid = $tid ? intval($tid) : null;
        $this->action = $this->tid ? 'modify' : 'add';
    }

    /**
     * 钩子方法 帖子发布展示
     */
    public function createHtmlRightContent() {
        if(!$this->checkPrivilege()) return false;

        if($this->action == 'modify'){
            Wind::import('SRC:library.Pw');
            if($this->tid && $this->_getEncryptPostsDs()->get($this->tid)){
                $this->ifCheck = true;
            } 
            PwHook::template('displayUpdateHtml', 'EXT:encryptposts.template.update_encryptposts', true, $this);
        }elseif($this->action == 'add' ) {
            PwHook::template('displayPostHtml', 'EXT:encryptposts.template.post_encryptposts', true, $this);
        }
    }


    /**
    * 发表帖子 钩子
    */
    public function addThread($tid){
        $data = $this->inputData;
        if(intval($data['isopen'] != 1)) return false;

        $tid = intval($tid);
        if($tid < 1) return false;
        $token = strtolower(WindUtility::generateRandStr(10));
        $dm = $this->_getEncryptPostsDm();
        $dm->setTid($tid)
           ->setToken($token);

        return $this->_getEncryptPostsDs()->add($dm);

    }


    /**
     *帖子更新 钩子
     */
    public function updateThread($tid){
        $tid = intval($tid);
        if($tid < 1) return false;
        if(!$this->checkPrivilege()) return false;
        $data = $this->inputData;
        $isOpen = intval($data['isopen']);
        if(!$isOpen){
            //关闭
            $this->_getEncryptPostsDs()->delete($tid);
        }else{
            $info = $this->_getEncryptPostsDs()->get($tid);
            if(!$info){
                $this->addThread($tid);
            }
        }
        return true;
    }




    public function checkPrivilege(){
        if($this->_getEncryptPostsConfig()->getConfigByGid()){
            return true;
        }else{
            return false;
        }   
    }


    public function setEncryptPostsInput($input){
    	$this->inputData = $input;
    }

    private function _getEncryptPostsDs(){
    	return Wekit::load('EXT:encryptposts.service.App_EncryptPosts_EncryptPosts');
    }

    private function _getEncryptPostsDm(){
    	return Wekit::load('EXT:encryptposts.service.dm.App_EncryptPosts_EncryptPostsDm');
    }

    private function _getEncryptPostsConfig(){
        return Wekit::load('EXT:encryptposts.service.srv.App_EncryptPosts_ConfigDo');
    }

}

?>