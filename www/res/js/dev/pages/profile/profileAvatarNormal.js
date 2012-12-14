/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-头像普通上传
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), global.js, TAG_DEL
 * $Id$
 */
 
Wind.use('ajaxForm', function(){
	var avatgar_normal_btn = $('#J_avatgar_normal_btn');

	$('#J_avatgar_normal_form').ajaxForm({
		beforeSubmit : function(){
			Wind.Util.ajaxBtnDisable(avatgar_normal_btn);
		},
		dataType : 'json',
		success : function(data){
			Wind.Util.ajaxBtnEnable(avatgar_normal_btn);
			if(data.state == 'success') {
				Wind.Util.formBtnTips({
					wrap : avatgar_normal_btn.parent(),
					msg : data.message
				});
			}else if(data.state == 'fail'){
				Wind.Util.formBtnTips({
					error : true,
					wrap : avatgar_normal_btn.parent(),
					msg : data.message
				});
			}
		}
	});
});