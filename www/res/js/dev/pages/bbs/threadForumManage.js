/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-帖子列表操作
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), jquery.form, TID
 * $Id: threadForumManage.js 22463 2012-12-24 11:28:52Z hao.lin $
 */

Wind.use('dialog', function(){
	var dialog_post = $('.J_post_manage_col a:not(.J_manage_single)');		//管理操作项

	//帖子菜单
	$('li.J_menu_drop').on('mouseenter', function(e){
		$(this).children('div.J_menu_drop_list').show();
	}).on('mouseleave', function(e){
		$(this).children('div.J_menu_drop_list').hide();
	});

	//var	iframe_poped = false,											//表示帖子面板里的具体操作未弹出
	var posts_checkbox = $('#J_posts_list input.J_check'),		//所有帖子选择框
		post_manage_main = $('#J_post_manage_main'),					//帖子操作面板
		post_checked_count = $('#J_post_checked_count'),				//帖子操作面板里的选中篇数
		is_ie6 = $.browser.msie && $.browser.version < 7,				//ie6
		checkall = $('input.J_check_all');								//全选
	
	posts_checkbox.prop('checked', false);
	checkall.prop('checked', false);

	//点击帖子框
	posts_checkbox.on('change', function() {
		var $this = $(this), checked_length = posts_checkbox.filter(':checked').length;

		//选中篇数
		post_checked_count.text(checked_length);
		$('#J_manage_checked_count').text(checked_length);
		
		//判断选择&取消复选框，帖子操作面板或面板里的具体操作是否已弹出
		if(this.checked) {
			$this.parents('tr').addClass('tr_check');

			if($('#J_post_manage_main:visible').length || $('#J_posts_manage_pop').length) {
				//已经弹出
				return;
			}

			if(dialog_post.length && dialog_post.length <=2) {
				//小于1 不弹窗(1+1)
				return;
			}

			if(is_ie6) {
				Wind.Util.popPos(post_manage_main);
			}else{
				post_manage_main.css({
					position : 'fixed',
					top : ($(window).height() - post_manage_main.height())/2,
					left : ($(window).width() - post_manage_main.width())/2
				}).show();
			}
			
			//窗口拖动
			Wind.use('draggable', function(){
				post_manage_main.draggable( { handle : '.pop_top'} );
			});
		}else if(!this.checked) {
			$this.parents('tr.tr_check').removeClass('tr_check');

			if(!checked_length) {
				//取消所有复选框
				post_manage_main.hide();
				Wind.dialog.closeAll();
			}
		}
		
		
	});
	

	//帖子操作面板_全选&取消全选
	$('#J_post_manage_checkall').on('click', function(e) {
		e.preventDefault();
		var $this = $(this);
		
		if($this.text() === '全选') {
		
			posts_checkbox.attr('checked', 'checked');
			checkall.attr('checked', 'checked');
			$this.text('取消全选');
			post_checked_count.text(posts_checkbox.length);
			posts_checkbox.parents('tr').addClass('tr_check');
		}else{
		
			posts_checkbox.removeAttr('checked');
			checkall.removeAttr('checked');
			$this.text('全选');
			post_checked_count.text('0');
			posts_checkbox.parents('tr.tr_check').removeClass('tr_check');
		}
		
	});

	checkall.on('click', function() {
		$('#J_post_manage_checkall').click();
		if(!dialog_post.length || dialog_post.length >2) {
			//管理项大于1 弹窗
			if(this.checked) {
				Wind.Util.popPos(post_manage_main);
			}else{
				post_manage_main.hide();
			}
		}
	});
	
	
	//关闭帖子操作面板
	$('#J_post_manage_close').on('click', function(e){
		e.preventDefault();
		post_manage_main.hide();

		closeCheck();
	});
	
	//帖子操作iframe弹窗，考虑创建前台的common.js
	dialog_post.on( 'click',function(e) {
		e.preventDefault();
		var posts_checked = posts_checkbox.filter(':checked'),
			role = $(this).parents('.J_post_manage_col').data('role'),
			type = $(this).data('type');

		//取消全选，未选择帖子时点击操作弹出提示
		if(!posts_checked.length && role !== 'readbar') {
			Wind.Util.resultTip({
				error : true,
				msg : '请至少选择一个帖子'
			});
			return false;
		}
		
		var $this = $(this),
			xid_arr = [];
			
		$.each(posts_checked, function(i, o){
			xid_arr.push($(this).val());
		});

		//区分传输数据
		var _data = {};
		if(role == "read") {
			//阅读页 楼层
			_data['tid'] = TID;
			_data['pids[]'] = xid_arr;
		}else if(role == "readbar"){
			//阅读页 帖子
			_data['tid'] = TID;
			_data['pids[]'] = 0;
		}else if(role == "list"){
			//帖子列表页
			_data['tids[]'] = xid_arr;
		}

		Wind.dialog.closeAll();
		Wind.Util.ajaxMaskShow();
		$.post($this.prop('href'), _data, function(data) {
			Wind.Util.ajaxMaskRemove();
			if(Wind.Util.ajaxTempError(data)) {
				return;
			}

			//成功
			Wind.dialog.html(data, {
				id : 'J_posts_manage_pop',
				isMask		: false,	//无遮罩
				isDrag : true,
				resize : false,
				callback	: function(){
					Wind.use('ajaxForm', function(){
						post_manage_main.hide();
						if($.isFunction(window.manageThreads)) {
							manageThreads();
						}else{
							Wind.js(GV.JS_ROOT +'pages/bbs/manageThreads.js?v='+ GV.JS_VERSION);
						}

						$('#J_sub_topped').data({
							'type': type,
							'role': role
						});
					});
				},
				onClose : function(){
					closeCheck();
				}
			});
			
		}, 'html');
		
	});
	
	//关闭核对
	function closeCheck(){
		if(checkall.prop('checked')) {
			$('#J_post_manage_checkall').click();
		}else{
			posts_checkbox.filter(':checked').click();
		}
	}
	
});