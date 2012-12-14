/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-实名认证
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), PROFILE_TYPE, VERIFY_TPL, EXTRES_ROOT 页面定义
 * $Id$
 */
 
;(function(){
	try{
		var profile_form = $('form.J_profile_form');

		//模板请求替换
		var tplSwitch = function (url, dl){
			if(url) {
				$.get(url, function(data){
					if(Wind.Util.ajaxTempError(data)) {
						return;
					}
					dl.hide().before(data);
				});

				Wind.js(EXTRES_ROOT+'/verify/js/authProfile.js?v=' +GV.JS_VERSION);
			}
		}

		var key;
		if(PROFILE_TYPE == 'profile_profile') {
			key = 'profile';
		}else{
			key = 'contact';
		}

		for(i in VERIFY_TPL[key]) {
			tplSwitch(VERIFY_TPL[key][i], profile_form.find('input[name='+ i +']').parents('dl'));
		}
	}catch(e){
		$.error(e);
	};
	
})();