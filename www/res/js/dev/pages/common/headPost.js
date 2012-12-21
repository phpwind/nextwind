/**
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-用头部发帖
 * @Author	: linhao87@gmail.com
 * @Depend	: core.js、jquery.js(1.7 or later)
 * $Id$
 */

;(function(){
	var forum_data = {},															//版块数据
			head_forum_ct = $('#J_head_forum_ct'),				//版块弹窗列表区
			post_to_forum = $('#J_post_to_forum'),				//发帖到_版块
			head_forum_sub = $('#J_head_forum_sub'),	//确定
			forum_ul,
			fid = '';

	if(!forum_data.data) {
		//请求版块数据
		$.post(GV.URL.FORUM_LIST, {
				'withMyforum' : 1
			}, function(data){
			if(data.state == 'success') {
				forum_data.data = $.parseJSON(data.data);

				//循环写入分类数据
				var cate_data = forum_data.data['cate'],		//分类数据
						cate_arr = [];
				for(i=0, len=cate_data.length;i<len;i++) {
					cate_arr.push('<li tabindex="0" role="option" class="J_cate_item" data-cid="'+ cate_data[i][0] +'" aria-label='+ cate_data[i][1] +'>'+ cate_data[i][1] +'</li>');
				}
				head_forum_ct[0].innerHTML = '<div class="source_forum" tabindex="0" role="combobox" aria-owns="J_forum_list" aria-label="选择要发帖版块的分类，按回车键选定，按tab键盘进行切换"><h4>选择分类</h4><ul id="J_forum_list">'+ cate_arr.join('') +'</ul></div><div class="target_forum" tabindex="0" role="combobox" aria-owns="J_forum_ul" aria-label="选择要发帖的版块，按回车键选定，按tab键盘进行切换"><h4>选择版块</h4><ul id="J_forum_ul"></ul></div>'
				forum_ul = document.getElementById('J_forum_ul');
			}
		}, 'json');
	}


	//点击分类
	head_forum_ct.on('click keydown', 'li.J_cate_item', function(e) {
		if(e.type === 'keydown' && e.keyCode !== 13) {
			return;
		}
		var current_cid = $(this).data('cid');

		$(this).addClass('current').siblings().removeClass('current');
		post_to_forum.text('');																								//发帖到_版块
		head_forum_sub.addClass('disabled').prop('disabled', 'disabled');		//确定按钮不可用

		//循环写入版块数据

		var data_forum = forum_data.data['forum'][current_cid],
				forum_arr = [];
		for(i=0,len=data_forum.length;i<len;i++) {
			forum_arr.push('<li tabindex="0" role="option" class="J_forum_item" data-fid="'+ data_forum[i][0] +'" aria-label='+ data_forum[i][1] +'>'+ data_forum[i][1] +'</li>');
		}
		forum_ul.innerHTML = forum_arr.join('');
		forum_ul.parentNode.focus();

	});

	//点击版块
	head_forum_ct.on('click keydown', 'li.J_forum_item', function(e) {
		if(e.type === 'keydown' && e.keyCode !== 13) {
			return;
		}else {
			e.preventDefault();
		}
		fid = $(this).data('fid');
		$(this).addClass('current').siblings('.current').removeClass('current');
		post_to_forum.text($(this).text().replace(/-/g, ''));								//发帖到_版块
		head_forum_sub.removeClass('disabled').removeProp('disabled');		//确定按钮可用
		if(e.type === 'keydown') {
			$('#head_forum_join').focus();
		}
	});

	//跳转发帖页
	head_forum_sub.on('click', function(e) {
		e.preventDefault();
		var $this = $(this),
				href = $this.data('url') +'&fid='+ fid,
				head_forum_join = $('#J_head_forum_join');

		if(head_forum_join.prop('checked')) {
			//加入版块
			$.post(head_forum_join.data('url'), {fid : fid}, function(data){
				location.href = href;
			}, 'json');
		}else{
			location.href = href;
		}
		/*$.getJSON(href, function(data){
			if(data.state == 'success') {
				location.href = href;
			}else{
				//global.js
				resultTip({
					error : true,
					msg : data.message[0],
					follow : $this
				});
			}
		});*/
	});

	//关闭
		$('#J_head_forum_close').on('click', function(e){
			e.preventDefault();
			$('#J_head_forum_pop').hide();
		});

})();