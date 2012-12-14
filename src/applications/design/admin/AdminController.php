<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: yanchixia $>
 * @author $Author: yanchixia $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: AdminController.php 17614 2012-09-07 03:14:46Z yanchixia $ 
 * @package 
 */
class AdminController extends AdminBaseController {
	
	public function run() {
		//$emo = Wekit::load('emotion.srv.PwEmotionService')->getAllEmotion();
		$content = "[s:64]dfgdfgdfgsdg[s:64]dd345345[s:37]345345345345[s:35][s:50][s:51][s:51]";
		$content = Wekit::load('emotion.srv.PwEmotionService')->replaceEmotion($content);
		echo $content;
	}
	
	public function addStructureAction() {
		$ds = Wekit::load('design.PwDesignStructure');
		$name = 'struct_1';
		$tpl = 
<<<HTML
		<div class="diy_box">
			<div class="hd">[title]</div>
			<div class="ct cc">
			</div>
		</div>
HTML;
		$ds->addInfo($name,$tpl);
		exit('ok');
	}
	
	public function addModuleAction() {
		$array = array(
			array('{id}','数据ID'),
			array('{url}','帖子URL'),
			array('{subject}','帖子标题'),
			array('{image}','附件图片'),
			array('{content}','帖子内容'),
			array('{createdtime}','发帖时间'),
			array('{lastposttime}','最后回复时间'),
			array('{replies}','回复数'),
			array('{hits}','浏览数'),
			array('{author}','发帖作者'),
			array('{authorid}','作者UID'),
			array('{avatar_small}','小头像'),
			array('{avatar_middle}','中头像'),
			array('{avatar_big}','大头像'),
		);
	}
	
	
	
	
}