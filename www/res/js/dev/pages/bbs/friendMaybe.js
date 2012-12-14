/*!
 * PHPWind PAGE JS
 * @Copyright Copyright 2011, phpwind.com
 * @Descript: 前台-可能认识的人
 * @Author	: linhao87@gmail.com, TID
 * @Depend	: jquery.js(1.7 or later), global.js
 * $Id$
 */


;(function(){
	//你可能感兴趣的人
	var friend_maybe = $('#J_friend_maybe'),
		friend_maybe_list = $('#J_friend_maybe_list'),			//列表
		friend_url = friend_maybe_list.data('url'),			//列表更新地址
		maybe_others = $('#J_friend_maybe_others').text();		//剩余容器 textarea


		//查看共同好友
		friend_maybe_list.on('click', 'a.J_friend_view', function(e){
			e.preventDefault();
			var $this = $(this),
				uid = $(this).data('uid'),
				related = $('#J_friend_related_' + uid),		//关联的共同好友项
				items = $this.parents('.J_friend_maybe_items');
				
			if(!related.length) {
				//还没有dom
				items.append('<div id="J_friend_related_'+ uid +'" class="related J_friend_related" style="display:none;" data-load="false"><span class="tips_loading">载入中...</span><div>');
				related = $('#J_friend_related_' + uid);
			}

			if(!related.is(':visible')) {
				//展示共同好友项
				var all_views = $('div.J_friend_related:visible').prev().find('a.J_friend_view');
				all_views.text(all_views.text().replace('↑','↓'));
				$('div.J_friend_related:visible').slideUp();

				related.slideDown();
				$this.text($this.text().replace('↓', '↑'));

				//未请求数据
				if(!related.data('load')) {
					related.data('load', true)
					$.post(this.href, function(data){
						if(Wind.Util.ajaxTempError(data)) {
							related.data('load', false);
							return false;
						}

						related.data('load', true).html(data);
						related.slideDown();
						$this.text($this.text().replace('↓', '↑'));
					}, 'html');
				}
			}else{
				related.slideUp();
				$this.text($this.text().replace('↑', '↓'));
			}
			
		});

		//加关注
		var lock = false;
		friend_maybe_list.on('click', 'a.J_friend_maybe_follow', function(e){
			e.preventDefault();
			var $this = $(this);

			if(lock) {
				return false;
			}
			lock = true;
			
			Wind.Util.ajaxMaskShow();
			$.post(this.href, function(data){
				Wind.Util.ajaxMaskRemove();
				if(data.state == 'success') {
					if($('.J_friend_maybe_items').length <= 1) {
						//关注完最后一条
						friend_maybe.fadeOut();
						return;
					}

					//更新列表
					$this.parents('.J_friend_maybe_items').html('<div class="pop_loading"></div>');

					$.post(friend_url, function(data){
						if(Wind.Util.ajaxTempError(data)) {
							return false;
						}



						friend_maybe_list.html(data);

						Wind.Util.avatarError(friend_maybe_list.find('img.J_avatar'));
					}, 'html');
				}else if(data.state == 'fail') {
					Wind.Util.resultTip({
						error : true,
						elem : $this,
						follow : true,
						msg : data.message
					});
				}

				lock = false;
			}, 'json');
		});

})();