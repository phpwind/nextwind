/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-头像普通上传
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), global.js, TAG_DEL
 * $Id$
 */
 
Wind.use('ajaxForm', function(){
	var avatgar_normal_btn = $('#J_avatgar_normal_btn'),
		error_map = {
			'-90' : '请求超时',
			'-91' : '请求错误',
			'-92' : '请求错误',
			'-93' : '服务器错误',
			'-80' : '上传失败',
			'-81' : '上传类型错误',
			'-82' : '文件大小错误',
			'-83' : '文件大小超出限制',
			'-84' : '文件错误'
		};

	$('#J_avatgar_normal_form').ajaxForm({
		beforeSubmit : function(){
			Wind.Util.ajaxBtnDisable(avatgar_normal_btn);
		},
		dataType : 'json',
		success : function(data){
			Wind.Util.ajaxBtnEnable(avatgar_normal_btn);
			if(data == '1') {
				Wind.Util.formBtnTips({
					wrap : avatgar_normal_btn.parent(),
					msg : '上传成功'
				});
			}else{
				var msg;
				data = String(data);
				if(error_map[data]) {
					msg = error_map[data];
				}else{
					msg = '上传出错'
				}
				
				Wind.Util.formBtnTips({
					error : true,
					wrap : avatgar_normal_btn.parent(),
					msg : msg
				});

			}
		}
	});
});