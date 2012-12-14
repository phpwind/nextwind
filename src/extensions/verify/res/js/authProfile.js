/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-设置-实名认证
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), ajaxForm
 * $Id$
 */
 
;(function(){
	var item,
		tit,
		type;

	var authProfile = {
		itemsClick : function(href){
			//点击认证项
			var _this = this;

			Wind.Util.ajaxMaskShow();
			$.post(href, function(data){
				Wind.Util.ajaxMaskRemove();
				if(Wind.Util.ajaxTempError(data)) {
					return;
				}

				Wind.dialog.closeAll();

				Wind.dialog.html(data,{
					title : tit,
					isDrag : true,
					isMask : false,
					follow : item.is(':visible') ? item : undefined,
					zIndex : 9,
					callback : function(){
						_this.postCall()
					},
					onClose : function(){
						$('#J_email_list').remove();
					}
				});
			}, 'html');
		},
		postCall : function(tit){
			//认证发送回调
			var _this = this;
			Wind.use('ajaxForm', function(){
				$('form.J_auth_form').on('submit', function(e){
					e.preventDefault();
					var form = $(this),
						btn = form.find('button:submit');

					form.ajaxSubmit({
						dataType : 'json',
						beforeSubmit : function(){
							Wind.Util.ajaxBtnDisable(btn);
							form.find('.J_tips_btn').remove();

							$('#J_reg_tip__mobile').hide();
						},
						success : function(data){
							Wind.Util.ajaxBtnEnable(btn);
							if(data.state == 'success') {
								_this.successHandle(data, form, btn);
							}else if(data.state == 'fail'){
								Wind.Util.formBtnTips({
									error : true,
									wrap : btn.parent(),
									msg : data.message
								});
							}
						}
					});
				});
			});

			if(type == 'email') {
				Wind.use('emailAutoMatch', function(){
					$('#J_auto_email').emailAutoMatch();
				});
			}

			if(type == 'mobile') {
				_this.mobileInit();
			}

		},
		successHandle : function(data, form, btn){
			//认证提交成功回调
			if(type == 'email') {
				form.find('.pop_cont').hide().after('<div class="pop_cont J_auth_email_popel"><dl style="padding:20px;"><dt style="width:40px;"><span class="icon_middle_warning"></span></dt><dd class="f14">已经发送一封认证邮件到：<br>'+ data.data.email +'<br>点击邮箱中的认证链接访问即可通过验证。<br>如果长时间没有收到邮件，你可以<a href="" id="J_email_auth_resend">再试一次</a></dd></dl></div>');
				form.find('.pop_bottom').hide().after('<div class="pop_bottom J_auth_email_popel"><button type="button" class="btn J_auth_popclose">关闭</button></div>');

				$('#J_email_auth_resend').on('click', function(e){
					e.preventDefault();
					$('.J_auth_email_popel').prev().show().next().remove();
				});
			}else if(type == 'checkpwd'){
				var arr= new Array(),
					name = item.data('name');
				arr = name.split(",");

				tit = arr[0];
				type = arr[1];
				authProfile.itemsClick(data.referer+'&statu='+data.data.statu);
			}else if(type == 'alipay'){
				if(data.state == 'success') {
					location.href = data.data.referer;
				}else if(data.state == 'fail') {
					Wind.Util.formBtnTips({
						wrap : btn.parent(),
						msg : data.message
					});
				}
			}else{
				Wind.Util.formBtnTips({
					wrap : btn.parent(),
					msg : data.message,
					callback : function(){
						location.reload();
					}
				});
			}

			//关闭弹窗
			form.on('click', 'button.J_auth_popclose', function(e){
				e.preventDefault();
				Wind.dialog.closeAll();
			});
		},
		mobileInit : function(){
			//手机验证初始化
			var reg_mobile = this.reg_mobile = $('#J_reg_mobile'), //手机号码input
		        show_mcode = this.show_mcode = $('#J_show_mcode'), //获取手机验证码按钮
		        mcode_resend = this.mcode_resend = $('#J_mcode_resend'), //重新发送按钮
		        send_mobile = this.send_mobile = $('#J_send_mobile'), //填写的手机号码
		        mcode_tip = this.mcode_tip = $('#J_mcode_tip'), //手机验证码输入提示
		        reg_tip__mobile = this.reg_tip__mobile = $('#J_reg_tip__mobile'), //手机号错误提示
		        reg_mobileCode = this.reg_mobileCode = $('#J_reg_mobileCode'), //手机验证码input
		        counttime = this.counttime = parseInt(reg_mobile.data('counttime')), //倒计时间 秒
		        count_timer = this.count_timer;

		    var _this = this;

			reg_mobile.val('');
		    reg_mobile.prop('disabled', false);

		    var m_timer,
		    	regexp = /^(13[0-9]|15[0-9]|18[0-9])\d{8}$/,
		        checkin = false,
		        _v;

		    reg_mobile.on('focus', function(){
		    	//手机输入聚焦
		        var $this = $(this);
		        reg_tip__mobile.hide();
		        $('form.J_auth_form').find('.J_tips_btn').remove();
		        //计时器开始
		        m_timer = setInterval(function(){
		            var trim_v = $.trim($this.val());

		            if(trim_v.length == 11 && regexp.test(trim_v)) {
		                //手机格式验证通过

		                if(checkin || trim_v == _v) {
		                    //后端已验证或查询值重复
		                    return;
		                }
		                checkin = true

		                $.post(M_CHECK_MOBILE,{
		                    mobile : trim_v
		                }, function(data){
		                    _v = trim_v;
		                    checkin = false;
		                    if(data.state == 'success') {
		                        show_mcode.show();
		                        $('#J_reg_mobile_hide').val(trim_v);
		                        reg_tip__mobile.hide().empty();
		                    }else if(data.state == 'fail') {
		                        reg_tip__mobile.html('<span class="tips_icon_error">'+ data.message +'</span>').show();
		                    }
		                }, 'json');
		                /**/
		            }else{
		                show_mcode.hide();
		                reg_tip__mobile.hide();
		            }
		        }, 200);

		    }).on('blur', function(){
		        //输入失焦，解除计时
		        clearInterval(m_timer);

		        var trim_v = $.trim($(this).val());
		        reg_tip__mobile.show();

		        if(!trim_v) {
		        	reg_tip__mobile.html('<span class="tips_icon_error">手机号码不能为空</span>');
		        	return;
		        }

		        if(trim_v.length !== 11 || !regexp.test(trim_v)) {
		            //手机号错误提示
		            reg_tip__mobile.html('<span class="tips_icon_error">请正确填写您的手机号码</span>');
		            return;
		        }
		        
		    });

		    //获取验证码
		    show_mcode.on('click', function(e){
		        e.preventDefault();
		        reg_mobile.prop('disabled', true).addClass('disabled');

		        _this.mobileCheck(show_mcode, function(){
		            show_mcode.hide();
		            mcode_tip.show();
		            $('#J_reg_tip_mobile').empty();
		            reg_mobileCode.focus();
		            send_mobile.text(reg_mobile.val());
		            _this.mobileCountDown();
		        });
		    });

		    //修改号码
		    $('#J_mobile_change').on('click', function(e){
		    	e.preventDefault();
		    	reg_mobile.prop('disabled', false).removeClass('disabled').val('').focus();
		        mcode_tip.hide();
		        clearInterval(count_timer);
		        
		         //重置对比值
		        _v = undefined;
		    });

		    //重新发送
		    mcode_resend.on('click', function(e){
		    	e.preventDefault();

		    	if(!mcode_resend.hasClass('disabled')) {
		    		_this.mobileCheck(mcode_resend, function(){
		    			reg_mobileCode.focus();
			            _this.mobileCountDown();
			        });
		    	}
		    });
		},
		mobileCheck : function(elem, callback){
			var reg_mobile = this.reg_mobile;
        	//验证手机返回验证码
        	Wind.Util.ajaxBtnDisable(elem);
            $.post(M_CHECK, {mobile : reg_mobile.val()}, function(data){
            	Wind.Util.ajaxBtnEnable(elem);
                if(data.state == 'success') {
                    if(callback) {
                        callback();
                    }
                }else if(data.state == 'fail'){
                    Wind.Util.resultTip({
                        error : true,
                        follow : elem,
                        msg : data.message
                    });
                    reg_mobile.prop('disabled', false).removeClass('disabled');
                }
            }, 'json');
        },
        mobileCountDown : function(){
        	//倒计时
        	var _this = this,
        		c = _this.counttime,
        		mcode_resend = _this.mcode_resend,
        		count_timer = _this.count_timer;

        	mcode_resend.text(c+'秒后重新发送').prop('disabled', true).addClass('disabled');

        	count_timer = setInterval(function(){
        		c--;
        		mcode_resend.text(c+'秒后重新发送');
        		if(c <= 0) {
        			clearInterval(count_timer);
        			mcode_resend.text('重新发送').prop('disabled', false).removeClass('disabled');
        			return;
        		}
        	}, 1000);
        }
	};


	Wind.use('dialog', function(){
		$(document).on('click', 'a.J_auth_items', function(e){
			e.preventDefault();
			item = $(this);
			type = item.data('type'),
			tit = item.data('tit');

			authProfile.itemsClick(this.href);
		});
	});
	
	
})();