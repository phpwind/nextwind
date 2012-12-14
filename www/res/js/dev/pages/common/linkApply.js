/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-申请链接
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id$
 */

;(function(){
	var lock = false;
	$('a.J_link_apply').on('click', function(e){
			e.preventDefault();
			var $this = $(this);

			if($('#J_link_apply_pop').length) {
				return false;
			}

			if(lock == true) {
				return false;
			}
			lock = true;

			$.post(this.href, function(data){
				if(Wind.Util.ajaxTempError(data)) {
					return false;
				}

				Wind.use('dialog', function(){
					Wind.dialog.html(data, {
						id : 'J_link_apply_pop',
						title : '申请链接',
						//cls : 'pop_login core_pop_wrap',
						position : 'fixed',
						isMask : false,
						isDrag : true,
						width : 450,
						callback : function(){
							var link_apply_btn = $('#J_link_apply_btn');
							Wind.use('ajaxForm', function(){
								$('#J_link_apply_form').ajaxForm({
									beforeSubmit : function(){
										Wind.Util.ajaxBtnDisable(link_apply_btn);
									},
									dataType : 'json',
									success : function(data){
										if(data.state == 'success') {
											Wind.Util.formBtnTips({
												wrap : link_apply_btn.parent(),
												msg : data.message,
												callback : function(){
													Wind.dialog.closeAll();
												}
											});
										}else if(data.state == 'fail') {
											Wind.Util.formBtnTips({
												error : true,
												wrap : link_apply_btn.parent(),
												msg : data.message
											});
										}
										Wind.Util.ajaxBtnEnable(link_apply_btn);
									}
								});
							});
							

							lock = false;
						}
					});
				});
			}, 'html');
		});

})();