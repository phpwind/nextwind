/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-阅读页话题
 * @Author	: linhao87@gmail.com, TID
 * @Depend	: jquery.js(1.7 or later), global.js, ajaxForm
 * $Id$
 */


;(function(){
	var tag_temp_arrow = '<div class="arrow"><em></em><span></span></div>';
	var read_tag_item = $('a.J_read_tag_item');

	read_tag_item.each(function(){
		var $this = $(this);

		Wind.Util.hoverToggle({
			elem : $this,		//hover元素
			list : $this.next('.J_tag_card'),
			callback : function(elem, list){
				//定位
				list.css({
					left : elem.offset().left,
					top : elem.offset().top + elem.innerHeight() + 5
				});

				if(!elem.data('load')) {
					//未请求内容
					elem.data('load', true);
					$.post(elem.data('url'), function(data){
						if(Wind.Util.ajaxTempError(data)) {
							elem.data('load', false);
							return;
						}

						list.html(tag_temp_arrow + data);

						//关注&取消
						var lock = false;
						list.find('a.J_read_tag_follow').on('click', function(e){
							e.preventDefault();
							var $this = $(this),
								id = $this.data('id'),
								type = $this.data('type'),
								anti_type = (type == 'add' ? 'del' : 'add'),					//操作后 类型
								anti_text = (type == 'add' ? '取消关注' : '关注该话题'),		//操作后 文本
								anti_cls = (type == 'add' ? 'core_unfollow' : 'core_follow');	//操作后 class

							if(!GV.U_ID) {
								//未登录
								Wind.Util.quickLogin();
								return;
							}

							if(lock) {
								return;
							}
							lock = true;

							$.post(this.href, {
								id : id,
								type : type
							}, function(data){
								lock = false;
								if(data.state == 'success') {
									$this.text(anti_text).data('type', anti_type).removeClass('core_follow core_unfollow').addClass(anti_cls);
									Wind.Util.resultTip({
										elem : $this,
										follow : true,
										msg : data.message
									});
								}else if(data.state == 'fail') {
									Wind.Util.resultTip({
										error : true,
										elem : $this,
										follow : true,
										msg : data.message
									});
									list.hide();
								}
							}, 'json');
						});
					}, 'html')
				}

			}
		});
	});


	var read_tag_wrap = $('#J_read_tag_wrap'),
		read_tag_edit = $('#J_read_tag_edit');
	
	//编辑话题
	$('#J_read_tag_edit_btn').on('click', function(e){
		e.preventDefault();
		var li_arr = [];

		$.each($('a.J_read_tag_item'), function(i, o){
			var text = $(this).text();
			li_arr.push('<li><a href="javascript:;"><span class="J_tag_name">'+ text +'</span><del class="J_user_tag_del" title="'+ text +'">×</del><input type="hidden" name="tagnames[]" value="'+ text +'"></a></li>');
			
			read_tag_edit.find('ul.J_user_tag_ul').html(li_arr.join(''));
			
		});
		read_tag_edit.show();
		read_tag_wrap.hide();

		Wind.use('ajaxForm');
	});

	//编辑提交
	var btn = $('#J_read_tag_sub');
	btn.on('click', function(e){
		e.preventDefault();
		var $this = $(this);

		setTimeout(function(){
			Wind.use('ajaxForm', function(){
				$('#J_read_tag_form').ajaxSubmit({
					dataType : 'json',
					beforeSubmit : function(){
						Wind.Util.ajaxBtnDisable(btn);
					},
					success : function(data){
						if(data.state === 'success') {
							btn.text(data.message)
							Wind.Util.reloadPage(window);
						}else if(data.state === 'fail'){
							Wind.Util.ajaxBtnEnable(btn);
							Wind.Util.resultTip({
								error : true,
								elem : $this,
								follow : true,
								msg : data.message
							});
						}
					}
				});
			});
			
		}, 100);
	});

})();