<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase');
Wind::import('SRC:library.engine.error.PwError');


/**
 *
 * @author fengxiao <xiao.fengx@alibaba-inc.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_EncryptPosts_ReadService extends PwThreadDisplayDoBase{

	private $url;
    private $tid;
    private $fid;
    private $threadInfo;
    private $token;
    private $info;
    private $bp;
    private $tag = false;

    public function __construct(PwThreadDisplay $bp) {
        $this->loginUser = $bp->user;
        $this->threadInfo = $bp->thread->info;
        $this->tid = intval($bp->thread->tid);
        $this->fid = intval($bp->thread->fid);
        $this->info = $this->_getEncryptPostsDs()->get($this->tid);
        $this->bp = $bp;
    }

    /**
     *帖子阅读页 check钩子
     */
    public function check(){
        //1 不是加密帖子
        if(!$this->info) return true;
        //2 是本人发布
        $current_userid = intval($this->loginUser->uid);
        $created_userid = intval($this->threadInfo['created_userid']);
        if($current_userid == $created_userid) return true;
        //3 
        if($this->info['token'] == $this->token) return true;

        return new PwError('无法访问加密帖!');
    }

    public function bulidRead($read){
        if($this->tag == false){
            $this->bp->setUrlArg('token', $this->info['token']);
            $this->tag = true; 
        }
        return $read;
    }

    /**
     *钩子
     */
    public function createHtmlBeforeContent($read){
    	$tid = intval($read['tid']);
    	$pid = intval($read['pid']);
    	if($pid) return false;
    	if(!$this->info) return false;
    	$token = $this->info['token'];
    	$this->url = WindUrlHelper::createUrl('bbs/read/run',array('tid'=>$tid,'fid'=>$this->fid,'token'=>$token));
    	PwHook::template('displayReadHtml', 'EXT:encryptposts.template.read_encryptposts', true, $this);
    }


    public function setToken($token){
        $this->token = $token;
    }

    public function getUrl(){
    	return $this->url;
    }

    private function _getEncryptPostsDs(){
    	return Wekit::load('EXT:encryptposts.service.App_EncryptPosts_EncryptPosts');
    }

    private function _getEncryptPostsDm(){
    	return Wekit::load('EXT:encryptposts.service.dm.App_EncryptPosts_EncryptPostsDm');
    }

}

?>