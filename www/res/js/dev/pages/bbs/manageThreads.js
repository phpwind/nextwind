/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-帖子管理
 * @Author	: linhao87@gmail.com
 * @Depend	: jquery.js(1.7 or later), jquery.form, TID
 * $Id: manageThreads.js 21606 2012-12-11 11:33:10Z hao.lin $
 */
manageThreads();
function manageThreads(){
	
	//点击“置顶”等文字显示&隐藏相应内容
	$('label.J_toggle').on('click', function(e){
			$(this).parents('li').addClass('current')
				.siblings('li.current').removeClass('current');
	});
	
	$('input.J_toggle').on('change', function(){
		var $this = $(this);
		if($this.attr('checked')) {
			$this.parents('li').addClass('current')
				.siblings('li.current').removeClass('current');
		}
	});
	
	//选择“取消置顶”后禁用有效期
	
	$('#J_topped_select').on('change', function(){
		var v = $(this).val(),
			topped_time = $('#J_topped_time'),
			J_topped_forums = $('#J_topped_forums');
		if(v === '0') {
			topped_time.addClass('disabled').attr('disabled', 'disabled').val('');
			J_topped_forums.hide();
		}else if(v === '3') {
			J_topped_forums.show();
		}else{
			topped_time.removeClass('disabled').removeProp('disabled');
			J_topped_forums.hide();
		}
	});
	
	//加亮 字体
	$('a.J_font_style').on('click', function(e){
		e.preventDefault();
		var $this = $(this);
		$this.toggleClass('current');
		if($this.hasClass('current')) {
			$('#' +$this.data('id')).attr('checked', 'checked');
		}else{
			$('#' +$this.data('id')).removeAttr('checked');
		}
	});
	
	//提前时间，仅限数字，判断输入或粘贴
	var uptime = $('#J_uptime');
	if(uptime.length) {
		uptime.on('keyup', function(e){
			var $this = $(this), v = $this.val();
			v = v.replace(/[^\d]/g,'');
			$this.val(v);
		});
	
		uptime[0].onpaste = function(){
			setTimeout(function(){
				var v = uptime.val();
				v = v.replace(/[^\d]/g,'');
				uptime.val(v);
			}, 150);
		}
	}
	
	var posts_manage_pop = $('#J_posts_manage_pop'),
		date = posts_manage_pop.find('input.J_date'),
		datetime = posts_manage_pop.find('input.J_datetime');
	if(date.length || datetime.length) {
		Wind.use('datePicker',function() {
			date.datePicker({
				time : false
			});
			datetime.datePicker({
				time : true
			});
		});
	}

	var color_pick = posts_manage_pop.find('.J_color_pick');
	if(color_pick.length) {
		Wind.use('colorPicker', function() {
			color_pick.each(function(){
				var bg_elem = $(this).find('.J_bg');

				$(this).colorPicker({
					zIndex : 12,
					default_color : 'url("'+ GV.URL.IMAGE_RES +'/transparent.png")',	//默认透明背景
					callback:function(color) {
						bg_elem.css('background',color);
						$(this).next('.J_hidden_color').val(color.length === 7 ? color : '');
					}
				});
			});
		});
	}
	
	
	//点击写入操作理由
	$('#J_resson_select').on('change', function(){
		$('#J_resson_input').val($(this).val());
	});
	
	//获取所有被选择帖子的fid并提交
	var btn = $('#J_sub_topped'),
		form = $('#J_post_manage_ajaxForm');
	form.on('submit', function(e){
		e.preventDefault();
		var checks = $('#J_posts_list input.J_check:checked'),
			xid_arr = [],
			role = btn.data('role'),
			type = btn.data('type'),
			_data = {};

		$.each(checks, function(){
			xid_arr.push($(this).val());
		});

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

		Wind.Util.ajaxBtnDisable(btn);
		form.ajaxSubmit({
			dataType	: 'json',
			data : _data,
			success		: function(data){
				Wind.Util.ajaxBtnEnable(btn);
				if(data.state === 'success') {
					Wind.dialog.closeAll();

					Wind.Util.resultTip({
						msg : data.message,
						callback : function(){
							if(data.referer) {
								location.href = data.referer;
							}else{
								if(role == "read") {
									//阅读页 楼层
									if(type == 'delete') {
										//删除
										checks.parents('div.J_read_floor').remove();
										window.location.reload();
									}
								}else if(role == 'readbar'){
									//阅读页 帖子
									if(type !== 'norefresh') {
										window.location.reload();
									}else{
										Wind.Util.creditReward();
									}
								}else if(role == 'list'){
									//列表页
									window.location.reload();
								}
							}
						}
					});
				}else if(data.state === 'fail'){
					Wind.Util.resultTip({
						follow : btn,
						error : true,
						msg : data.message
					});
				}
			}
		});
	});
	
/*
 * 复制
*/
	var topic_data = {},
			postmanage_topictype = $('#J_postmanage_topictype');

	$('#J_postmanage_forum').on('change', function(){
		var $this = $(this);
		$.post($this.data('url'), {fid : this.value}, function(data){
			if(data.state == 'success') {
				var postmanage_topic = $('#J_postmanage_topic'),
						t_arr = [];

				topic_data = data.data;
				if(topic_data) {
					for( i in topic_data) {
						t_arr.push('<option value="'+ i +'">'+ topic_data[i]['name'] +'</option>');
					}

					postmanage_topic.show();
					postmanage_topictype.html('<option value="0">请选择分类</option>' + t_arr.join(''));

					setSubTopicType(postmanage_topictype.val(), topic_data);
				}else{
					postmanage_topic.hide();
				}

			}else if(data.state == 'fail') {

			}
		}, 'json');
	});

	postmanage_topictype.on('change', function() {
		setSubTopicType(this.value, topic_data);
	});

	$('#J_postmanage_type_topictype').on('change', function(){
		setSubTopicType(this.value, THREAD_SORT);
	});

	//显示二级分类
	function setSubTopicType(v, data) {
		var postmanage_subtopictype = $('#J_postmanage_subtopictype');
		v = parseInt(v);

		if(!v) {
			postmanage_subtopictype.hide();
			return ;
		}
		
		var sub_data = data[v]['sub_type'];
		if(sub_data) {
			var sub_arr = [];
			sub_arr.push('<option value="0">请选择分类</option>');
			for( i in sub_data) {
				sub_arr.push('<option value="'+ i +'">'+ sub_data[i]['name'] +'</option>');
			}
			postmanage_subtopictype.show().html(sub_arr.join(''));
		}else{
			postmanage_subtopictype.hide();
		}
		
	}

	//推送
				$('#J_push_select_initiative').on('change', function(){
					$.post($(this).data('url'), {
							pageid : this.value,
							fromtype : document.getElementById('J_fromtype').value
						}, function(data){
						if(data.state == 'success') {
							$('#J_push_select_passive').html(data.data);
						}else if(data.state == 'fail') {
							//global.js
							Wind.Util.resultTip({
								error : true,
								msg : data.message
							});
						}
					}, 'json');
				});
	
}