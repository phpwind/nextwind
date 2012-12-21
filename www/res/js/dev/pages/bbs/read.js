/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-阅读页_常用交互
 * @Author	: linhao87@gmail.com, TID
 * @Depend	: jquery.js(1.7 or later), global.js
 * $Id$
 */

;
//图片附件显示 删除
(function(){
	$('span.J_attach_img_wrap').hover(function(){
		var $this = $(this);
		$this.find('.J_img_info').show().css({
			left : $this.offset().left,
			top : $this.find('img.J_post_img').offset().top
		});
	}, function(){
		$(this).find('.J_img_info').hide();
	});

	$('a.J_read_img_del').on('click', function(e){
		e.preventDefault();
		var $this = $(this);

		//glbal.js
		Wind.Util.ajaxConfirm({
			href : this.href,
			elem : $this,
			callback : function(){
				$this.parents('.J_attach_img_wrap').fadeOut(function(){
					$(this).remove();
				});
			}
		});
	});

})();

//显示喜欢过的人
(function(){
	$('a.J_like_user_btn').on('click', function(e){
		e.preventDefault();
		var $this = $(this),
			pid = $this.data('pid'),
			like_user_pop = $('#J_like_user_pop_'+ pid);

		//是否已存在下拉
		if(like_user_pop.length) {
			//下拉是否可见
			if($('#J_like_user_pop_'+ pid +':visible').length) {
				like_user_pop.hide();
			}else{
				like_user_pop.show();
			}

		}else{
			$.post($this.attr('href'), function(data){
				if(data.state === 'success') {
					var data = data.data,
						li_arr = [],
						template = $($('#J_like_user_ta').text()),
						this_offset_top = $this.offset().top,
						this_height = $this.innerHeight(),
						this_window_top = this_offset_top - $(document).scrollTop(),				//到窗口顶部距离
						this_window_bottom = $(window).height() - this_window_top - this_height,	//到窗口底部距离
						temp_top;

					$.each(data, function(i, o){
						li_arr.push('<li><a href="'+ GV.U_CENTER + o.uid +'"><img class="J_avatar" data-type="small" src="'+ o.avatar +'" width="30" height="30" />'+ o.username +'</a></li>');
					});

					template.appendTo('body').attr('id', 'J_like_user_pop_'+ pid).find('ul.J_like_user_list').html(li_arr.join(''));

					if (this_window_bottom < template.outerHeight()) {
						//底部空间不足，显示在上面
						temp_top = this_offset_top - template.outerHeight();
					}else{
						temp_top = this_offset_top + this_height;
					}

					//写入位置
					template.css({
						top : temp_top,
						left : $this.offset().left
					});

					Wind.Util.avatarError(template.find('img.J_avatar'));

					//绑定关闭
					$('a.J_like_user_close').on('click', function(e){
						e.preventDefault();
						template.hide();
					});

				}else if(data.state === 'fail'){
					//global.js
					Wind.Util.resultTip({
						error : true,
						msg : data.message
					});
				}
			}, 'json');
		}
	});

})();

//发帖下拉
(function(){
	Wind.Util.hoverToggle({
		elem : $('#J_read_post_btn'),			//hover元素
		list : $('#J_read_post_types'),			//下拉菜单
		callback : function(elem, list){
			list.css({
				left : elem.offset().left,
				top : elem.offset().top + elem.height()
			});
		}
	});

	//只看楼主
	Wind.Util.hoverToggle({
		elem : $('#J_read_moredown'),			//hover元素
		list : $('#J_read_moredown_list'),		//下拉菜单
		callback : function(elem, list) {
			list.css({
				left : elem.offset().left + elem.width() - list.outerWidth(),
				top : elem.offset().top + elem.height()
			});
		}
	});

})();

//楼层拷贝
(function(){
	var floor_copy = $('.J_floor_copy');

	if(!$.browser.msie && !Wind.Util.flashPluginTest(9)) {
		if(confirm('您的浏览器尚未安装flash插件，楼层地址复制不可用！点击确定下载')) {
			location.href = 'http://get.adobe.com/cn/flashplayer/';
		};
		return;
	}

	Wind.use('textCopy', function() {
		for(i=0, len=floor_copy.length; i<len; i++) {
			var item = $(floor_copy[i]),
				type = item.data('type'),
				hash = (type == 'main' ? '' : '#'+item.data('hash')),		//楼层带hash
				par = item.parent();
			item.textCopy({
				content : location.protocol + '//' + location.host + location.pathname + location.search + hash,
				mouseover :function(client){
					client['div'].setAttribute('title', '复制此楼地址');
				},
				appendelem : par[0],
				addedstyle : {
					top : item.offset().top - par.offset().top,
					left : item.offset().left - par.offset().left
				}
			});
		}
	});

})();

//阅读页的代码高亮
(function(){
	//代码高亮公用接口
	window.HighLightFloor = {
		active: false,
		init: function(callback){
			var _this = this;
			var syntaxHihglighter_path = window.GV.JS_ROOT + 'windeditor/plugins/insertCode/syntaxHihglighter/';
			Wind.css(syntaxHihglighter_path + 'styles/shCoreDefault.css?v=' + GV.JS_VERSION);
			Wind.js(syntaxHihglighter_path +'scripts/shCore.js?v=' + GV.JS_VERSION,function() {
				_this.active = true;
				_this.render();
				callback && callback();
			});
		},
		render: function(){
			SyntaxHighlighter.highlight();
		},
		//渲染复制按钮
		renderCopy: function(elem, text){
			//复制代码
			if(elem.data('textCopy')){
				return;
			}
			elem.data('textCopy', 'true');
			Wind.use('textCopy', function() {
				setTimeout(function(){
					elem.textCopy({
						content : text
					});
				});
			});
		},
		addCopy: function(elem){
			var  _self = this,
				html = elem.innerHTML;
			html = html.replace(/&amp;/g, '&').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
			//ie下使用innerHTML会去掉所有空格
			$(elem).text(html);
			var copyElement = $('<br/><a role="button" href="javascript:;" rel="nofollow">复制代码</a>');
			copyElement.insertBefore(elem);
			copyElement.on('mouseover', function(){
				_self.renderCopy(copyElement, html);
			});
		},
		adjust: function(elem){
			if(elem){
	            var tds = elem.getElementsByTagName('td');
	            for(var i=0,li,ri;li=tds[0].childNodes[i];i++){
	                ri = tds[1].firstChild.childNodes[i];
	                if(ri) {
	                    ri.style.height = li.style.height = ri.offsetHeight + 'px';
	                }
	            }
	        }
		}
	};
	//代码高亮渲染
	var codes = $('pre[data-role="code"]');
	if(codes.length) {
		codes.each(function(){
			HighLightFloor.addCopy(this);
		});
		HighLightFloor.init(function(){
			$(".syntaxhighlighter").each(function(){
				HighLightFloor.adjust(this);
			})
		});
	}
})();

//大小图切换
;(function() {
	var attach_pics_list = $('div.read_attach_pic'),
		$doc = $(document);
	if( attach_pics_list.length ) {
		attach_pics_list.each(function() {
			var container = $(this);
			$(this).find('a.J_small_images').on('click',function(e) {
				e.preventDefault();
				$(this).removeClass('current');
				container.find('a.J_big_images').addClass('current');
				container.find('ul.big_img').hide();
				container.find('ul.small_img').show();
			});
			$(this).find('a.J_big_images').on('click',function(e) {
				e.preventDefault();
				$(this).removeClass('current');
				container.find('a.J_small_images').addClass('current');
				container.find('ul.small_img').hide();
				container.find('ul.big_img').show();
				$doc.scrollTop($doc.scrollTop()+1);
			});
		});
	}
})();

//前台管理日志
(function(){
	var inside_logs = $('#J_inside_logs');
	if(inside_logs.length) {
		Wind.use('dialog', function(){
			
			inside_logs.on('click', function(e){
				e.preventDefault();
				Wind.Util.ajaxMaskShow();
				
				$.post(this.href, function(data){
					Wind.Util.ajaxMaskRemove();
					if(Wind.Util.ajaxTempError(data, inside_logs)) {
						return;
					}

					Wind.dialog.html(data, {
						id : 'read_log',
						title : '帖子操作记录',
						isMask : false,
						isDrag : true,
						callback : function(){
							$('#J_log_close').on('click', function(e){
								e.preventDefault();
								Wind.dialog.closeAll();
							});
						}
					});
				});

			});
			
		});
	}
})();