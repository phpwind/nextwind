/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-阅读回复
 * @Author	: linhao87@gmail.com, TID
 * @Depend	: jquery.js(1.7 or later), global.js, userCard.js, report.js
 * $Id$
 */


;(function(){
	Wind.use('localStorage',function() {
		Wind.Util.LocalStorage.remove('quickReply');
	});
/*
 * 本地存储快速回复
 */
	function quickStorage($ele){
		Wind.use('localStorage',function() {
			var set = function() { 
				//不支持placeholder容错处理
				var val = $ele.val();
				if(document.createElement('input').placeholder !== ''){
					if(val === $ele.attr("placeholder")){
						return;
					}
				}
				
				Wind.Util.LocalStorage.set('quickReply',val);
			};
			if($.browser.msie) {
				$ele[0].onpropertychange = function(event) {
				    set();
				}
			}else {
				$ele.on('input',set);
			}
		});
	}
/*
 * 主楼快速回复
*/
	var reply_quick_ta = $('#J_reply_quick_ta'),
			reply_quick_btn = $('#J_reply_quick_btn'),
			reply_ft = $('#J_reply_ft'),
			read_0 = $('#read_0');				//回复主楼

	//global.js
	Wind.Util.buttonStatus(reply_quick_ta, reply_quick_btn);
	Wind.Util.ctrlEnterSub(reply_quick_ta, reply_quick_btn);

	//回复框聚焦
	reply_quick_ta.on('focus', function() {
		reply_ft.fadeIn();
		//需要记录用户的输入，点击进入高级模式时需要
		quickStorage(reply_quick_ta);
	});
	//楼层快速回复框自动保存数据
	$(document).on('focus', '.J_at_user_textarea', function(){
		quickStorage($(this));
	})
	
	//提交回复
	reply_quick_btn.on('click', function(e){
		e.preventDefault();
		//清除本地存储
		if(Wind.Util.LocalStorage && Wind.Util.LocalStorage.get('quickReply') !== null){
			Wind.Util.LocalStorage.remove('quickReply');
		}
		//end
		var $this = $(this);
		//global.js
		Wind.Util.ajaxBtnDisable($this);

		$.post($(this).data('action'), {
			atc_content : reply_quick_ta.val(),
			tid : $(this).data('tid')
		}, function(data){
			//global.js
			Wind.Util.ajaxBtnEnable($this);
			if (Wind.Util.ajaxTempError(data, $this)) {
				if(data.indexOf('审核') > 0) {
					reply_ft.fadeOut();
					reply_quick_ta.val('');
					$('#J_emotions_pop').hide();
				}
				return false;
			}

			if($('#J_need_reply').length) {
				//回复可见
				Wind.Util.reloadPage(window);
			}

			reply_ft.fadeOut();
			reply_quick_ta.val('');
			read_0.after(data);
			$('#J_emotions_pop').hide();
			//高亮代码start
			var highlightFunc = function(){
				var nextFloor = read_0.nextAll('.J_read_floor').eq(0);
				var codes = $('pre[data-role="code"]', nextFloor);
				if(codes.length) {
					codes.each(function(){
						//console.log(this)
						HighLightFloor.addCopy(this);
					});
					HighLightFloor.render();
					$(".syntaxhighlighter").each(function(){
						HighLightFloor.adjust(this);
					});
				}
			};
			var nextFloor = read_0.nextAll('.J_read_floor').eq(0);
			//保证当HighLightFloor存在的时候才会渲染，防止当文件变更等原因导致报错
			if(typeof HighLightFloor !== 'undefined'){
				if(HighLightFloor.active === true){
					highlightFunc();
				}else{
					HighLightFloor.init(highlightFunc);
				}
			}
			//高亮end
			
			var new_floor = $('#read_0 ~ .J_read_floor').first();

			//回复楼的喜欢
			Wind.js(GV.JS_ROOT+ 'pages/common/likePlus.js?v='+ GV.JS_VERSION, function () {
				likePlus(new_floor.find('a.J_like_btn'));
			});
			

			//头像
			Wind.Util.avatarError(new_floor.find('img.J_avatar'));

			//积分提示
			Wind.Util.creditReward();
		});
	});


/*
 * 查看回复
*/
	var lock = false,
			posts_list = $('#J_posts_list');

	posts_list.on('click', 'a.J_read_reply', function(e){
		e.preventDefault();
		var $this = $(this),
			pid = $this.data('pid'),
			topped = $this.data('topped'),
			wrap = $('#J_reply_wrap_'+ pid + (topped ? '_topped' : ''));			//列表容器

		wrap.toggle();

		//锁定 或 已请求
		if(lock || $this.data('load')) {
			wrap.find('.J_at_user_textarea').val('').focus();
			return false;
		}
		lock = true;

		$.post(this.href, function(data){
			//global.js
			lock = false;
			if(Wind.Util.ajaxTempError(data))	{
				return false;
			}

			wrap.html(data);
			//location.hash = 'read_'+ pid;			//锚点跳转
			$this.data('load', true);					//已请求标识

			replyFn(wrap);

			//ie6初次展开不聚焦
			wrap.find('textarea').focus();

			Wind.Util.avatarError(wrap.find('img.J_avatar'));
			
		});
	});

	
	posts_list.on('click', 'a.J_insert_emotions' ,function(e){
		//表情
		e.preventDefault();
		var $this = $(this);
		Wind.js(GV.JS_ROOT +'pages/common/insertEmotions.js?v='+ GV.JS_VERSION, function(){
			insertEmotions($this, $($this.data('emotiontarget')));
		});
	}).on('click', 'a.J_read_reply_single' ,function(e){
		//回复单个
		e.preventDefault();
		//var wrap = $(this).parents('div.J_reply_wrap'),
		var wrap = $(this).parents('.J_reply_wrap'),
				username = $(this).data('username'),
				textarea = wrap.find('textarea');

			textarea.focus().val('@'+ username +'：');
			if(!$.browser.msie) {
				//chrome 光标定位最后
				textarea[0].setSelectionRange(100,100);
			}
	}).on('click', 'button.J_reply_sub' ,function(e){
		//提交
		e.preventDefault();
		var $this = $(this),
			pid = $this.data('pid'),
			par = $this.parents('.J_reply_wrap'),
			textarea = par.find('textarea'),
			list = par.find('.J_reply_page_list ul');

		//global.js
		Wind.Util.ajaxBtnDisable($this);

		$.post($(this).data('action'), {
			atc_content : textarea.val(),
			tid : TID,
			pid : pid
		}, function(data){
			//global.js
			Wind.Util.ajaxBtnEnable($this, 'disabled');

			if(Wind.Util.ajaxTempError(data)) {
				/*textarea.val('');
				$this.addClass('disabled').prop('disabled', true);
				$('#J_emotions_pop').hide();*/
				if(data.indexOf('审核') > 0) {
					textarea.val('');
					$('#J_emotions_pop').hide();
				}
				return false;
			}

			if($('#J_need_reply').length) {
				//回复可见
				location.reload();
			}

			list.prepend(data);
			textarea.val('');
			$('#J_emotions_pop').hide();

			/*location.hash = 'read_'+ pid;*/			//锚点跳转

			//积分奖励
			Wind.Util.creditReward();
			
		});
	}).on('click', 'div.J_pages_wrap a' ,function(e){
		//翻页
		e.preventDefault();
		var list = $(this).parents('.J_reply_page_list'),
				clone = list.clone();

		//跳楼
		/*location.hash = $(this).parents('.J_read_floor').attr('id');*/
		
		list.html('<div class="pop_loading"></div>');

		$.post(this.href, function(data){
			if(Wind.Util.ajaxTempError(data)) {
				//失败则恢复原内容
				list.html(clone.html());
				return false;
			}

			list.html(data);
		})
	});


/*
 *回复列表公共方法
*/
	function replyFn(wrap){
		var btn = wrap.find('button.J_reply_sub'),
			ta = wrap.find('textarea');
		Wind.Util.buttonStatus(ta, btn);
		Wind.Util.ctrlEnterSub(ta, btn);
		ta.focus();
	}

})();