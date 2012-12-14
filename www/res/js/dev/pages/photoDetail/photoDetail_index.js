/*!
 * PHPWind UI Library 
 * @Copyright 	: Copyright 2011, phpwind.com
 * @Descript	: 相册图片浏览
 * @Author		: siweiran@gmail.com
 * @Depend		: core.js、jquery.js(1.7 or later)
 * $Id: dialog.js 8433 2012-04-18 12:23:53Z chris.chencq $			:
 */;
(function () {
    var preview, imgContent = $("#imgContent"),
        pName = $("#photoName"),
        pDescript = $("#photoDescrip"),
        commtentsNum = $("#photo_comments_num"),
        comment_nums = $("#comment_nums"),
        _pre = $("#photo_pre"),
        _next = $("#photo_next");

    function loadImage() {
        imgContent.attr("src", preview.src);
    }

    function showAjaxData(url, data, type, dataType, sucCallBack) {
        $.ajax({
            url: url,
            data: data,
            type: type ? type : "POST",
            dataType: dataType,
            success: function (data) {
                sucCallBack(data);
            },
            error: function () {
                Wind.Util.resultTip({
                    error: true,
                    msg: "请求出错,请重试",
                    follow: false
                });
            }
        })
    }
    var slider = {
        photoList: $("#photo_mini_list"),
        photo_list_item: $("#photo_mini_list li"),
        pic_edit: $("#pic_edit"),
        pic_del: $("#pic_del"),
        pic_handle: $("#curPicId"),
        //评论输入框
        comment_content: $("#photo_comments"),
        commentV: $("#J_fresh_post_ta"),
        //每版显示10条
        page: 10,
        //单位缩略图间距
        dis: 59,
        //每次移动5条
        perNum: 5,
        //总页数
        pages: Math.ceil($("#photo_mini_list li").length / 5),
        //当前第几页
        curPage: 0,
        init: function () {
            this.changHash();
            var _this = this,
                curId = location.hash.substr(1);
            //重写Ul的宽度
            _this.photoList.css("width", _this.photo_list_item.length * _this.dis);
            //确定当前小图的位置
            _this.setPosition($("#photo_" + curId));
            //左右按钮绑定事件
            _pre.bind('click', function (e) {
                e.preventDefault();
                var left = _this.photoList.css("left");
                if (_this.curPage >= 2 && left != "0px") {
                    _this.move("right");
                } else {
                    return false;
                }
            })
            _next.bind('click', function (e) {
                e.preventDefault();
                if (_this.curPage <= _this.pages - 1) {
                    _this.move("left");
                } else {
                    return false;
                }
            })
            //点击缩略图
            $("#photo_mini_list a").bind('click', function (e) {
                e.preventDefault();
                var $this = $(this);
                _this.displayImage($this);
            })
            $("#nextPic").bind('click', function () {
                _this.nextPic();
            })
            $("#prePic").bind('click', function () {
                _this.prePic();
            })
            _this.operatePic();
            _this.submitComment();
            
            //删除回复操作
            _this.comment_content.find('.pop_close').live('click', function (e) {
                e.preventDefault();
                var $this = $(this),
                    $id = $this.attr('data-del');
                _this.deleteComment($this, $id);
            })
            _this.report();
        },
        //改变url的hash值,用于刷新页面后显示当前页面
        changHash: function () {
            var _this = this,
                currentId = _this.pic_handle.val(),
                hash = (!window.location.hash) ? "#" + currentId : window.location.hash;
            window.location.hash = hash;
        },
        //点击缩略图显示大图 ele为a元素
        displayImage: function (ele) {
            var _this = this,
                url = ele.attr('data-img'),
                $id = ele.attr("data-id");
            ele.parent().addClass('current').siblings().removeClass('current');
            //先执行onload事件然后再给src赋值
            preview = new Image();
            preview.onload = loadImage;
            preview.onerror = function () {};
            preview.src = url;
            location.hash = $id;
            //改变ID,用于删除、编辑操作
            _this.pic_handle.val($id);
            _this.firstLastMove(ele.parent());
            _this.loadPicInfo(ele);
            _this.loadComment($id)
        },
        //对图片的编辑、删除操作
        operatePic: function () {
            var _this = this;
            _this.pic_del.bind('click', function (e) {
                e.preventDefault();
                _this.deletePic($(this));
            })
            _this.pic_edit.bind('click', function (e) {
                e.preventDefault();
                _this.editPic($(this));
            })
        },
        //获取当前图片id的索引 从1开始
        getSlideIndex: function (cur) {
            return this.photo_list_item.index(cur) + 1;
        },
        //根据当前索引计算left值
        computeLeft: function (cur) {
            var index = this.getSlideIndex(cur),
                page = this.page,
                dis = this.dis,
                left = 0,
                num = parseInt(index / page);
            if (index < page) {
                left = 0;
            }
            //如果当前显示的索引刚好是5的整数倍,小图居中显示,前四后五
            else if (index % page == 0) {
                left = ((num - 1) * page + 5) * dis;
            } else {
                left = num * page * dis;
            }
            return left;
        },
        //根据当前索引计算缩略图所在哪一页
        whichPage: function (cur) {
            var index = this.getSlideIndex(cur),
                num = index / this.perNum,
                _int = index % this.perNum;
            if (_int != 0) {
                return parseInt(num) + 1;
            } else {
                return num;
            }
        },
        //最左边或者最右边
        firstLastMove: function (ele) {
            var cur_li = ele,
                _this = this,
                cur_left = cur_li.position().left,
                p_left = parseInt(_this.photoList.css("left")),
                s_left = p_left < 0 ? Math.abs(p_left) : p_left,
                index = _this.getSlideIndex(cur_li),
                r_dis = (_this.page-1)*_this.dis;
            if (s_left == cur_left && index > 1) {
                _this.move('right');
            }
            if (s_left + r_dis === cur_left) {
                _this.move('left');
            }
        },
        setPosition: function (cur) {
            var left = this.computeLeft(cur),
                _left = "-" + left + "px",
                ele = cur.find('a');
            this.displayImage(ele);
            this.photoList.css({
                "left": _left
            });
            this.curPage = this.whichPage(cur);
            console.log(this.curPage)
            //this.buttonisActive();
        },
        nextPic: function () {
            var nextEl = this.photoList.find(".current").next(),
                link = nextEl.find("a");
            link.trigger('click');
        },
        prePic: function () {
            var preEl = this.photoList.find(".current").prev(),
                link = preEl.find("a");
            link.trigger('click');
        },
        move: function (dir) {
            //每次移动5个图片的距离
            var moveLen = 5 * this.dis;
            if (dir == "left") {
                this.photoList.animate({
                    left: '-=' + moveLen
                });
                this.curPage++;
                console.log("当前页" + this.curPage)
            } else {
                this.photoList.animate({
                    left: '+=' + moveLen
                });
                this.curPage--;
                console.log("当前页" + this.curPage)
            }
            this.buttonisActive();
        },
        buttonisActive: function () {
            if (this.curPage < this.pages - 1 && this.curPage > 2) {
                _pre.removeClass("pre_disabled");
                _next.removeClass("next_disabled");
            } else if (this.curPage == this.pages - 1) {
                _next.addClass("next_disabled");
                return false;
            } else if (this.curPage == 2) {
                _pre.addClass("pre_disabled");
                return false;
            }
        },
        //加载图片的标题以及评论
        loadPicInfo: function (ele) {
            pName.html(ele.attr("data-pname"));
            pDescript.html(ele.attr("data-pdes"));
        },
        //删除图片
        deletePic: function (ele) {
            var _this = this,
                id = _this.pic_handle.val();
            Wind.Util.ajaxConfirm({
                elem: ele,
                href: ele.attr('href') + "&photoid=" + id,
                msg: "确定要删除这张图片吗?",
                callback: function () {
                    Wind.Util.resultTip({
                        msg: '删除成功'
                    });
                    _this.nextPic();
                    $("#photo_" + id).remove();
                }
            });
        },
        //编辑图片
        editPic: function (ele) {
            var _this = this,
                id = _this.pic_handle.val(),
                url = ele.attr('href');
            var sucCallBack = function (data) {
                    if (!data.state) {
                        var $form = $(data);
                        $form.appendTo($("body"));
                        $form.find('.pop_close,.btn_close_edit').bind('click', function (e) {
                            e.preventDefault();
                            $form.remove();
                        });
                        _this.sendEditData($form);
                    } else {
                        Wind.Util.resultTip({
                            error: true,
                            msg: "数据返回有误",
                            follow: false
                        });
                    }
                }
            showAjaxData(url, {
                photoid: id
            }, "post", 'html', sucCallBack)
        },
        sendEditData: function ($obj) {
            $sub = $obj.find(".btn_submit"), $edit_photo = $("#edit_photo");
            $sub.bind('click', function (e) {
                e.preventDefault();
                var url = $obj.attr("action"),
                    formData = $obj.serialize(),
                    photoName = $edit_photo.find("input[name=name]").val(),
                    photoDescrip = $edit_photo.find("textarea[name=descrip]").val();
                var callBack = function (data) {
                        if (data.state === "success") {
                            Wind.Util.resultTip({
                                msg: "编辑成功"
                            });
                            window.location.reload();
                        } else {
                            Wind.Util.resultTip({
                                error: true,
                                msg: data.message,
                                follow: false
                            });
                        }
                    }
                showAjaxData(url, formData, null, 'json', callBack)
            })
        },
        //发表评论
        submitComment: function () {
            var _this = this,
                $btnSubmit = $("#btn_submit_comment"),
                $form = $("#photoCommentForm");
            $btnSubmit.click(function (e) {
                e.preventDefault();
                var $comment = $.trim(_this.commentV.val()),
                    url = $form.attr("action"),
                    csrf_token = $form.find("input[name=csrf_token]").val(),
                    id = _this.pic_handle.val();
                if ($comment == "") {
                    Wind.Util.resultTip({
                        error: true,
                        msg: '评论内容不能为空!'
                    });
                    return false;
                } else {
                    data = {
                        content: $comment,
                        photoid: id,
                        csrf_token: csrf_token
                    };
                    var sucCallBack = function (data) {
                            if (data.state === "success") {
                                _this.commentV.val('');
                                Wind.Util.resultTip({
                                    msg: "评论成功"
                                });
                                _this.loadComment(id);
                            } else {
                                Wind.Util.resultTip({
                                    error: true,
                                    msg: data.message,
                                    follow: false
                                });
                            }
                        }
                    showAjaxData(url, data, null, 'json', sucCallBack);
                }
            })
        },
        //加载评论
        loadComment: function (id) {
            var _this = this;
            var sucCallBack = function (data) {
                    if (!data.state) {
                        _this.comment_content.html(data);
                        //改写评论数字
                        var comms = $("#comment_num").html();
                        comment_nums.html("评论 (" + comms + ")");
                    } else {
                        Wind.Util.resultTip({
                            error: true,
                            msg: data.message,
                            follow: false
                        });
                    }
                }
            showAjaxData(url.loadCommentUrl, {
                photoid: id
            }, "post", 'html', sucCallBack);
        },
        //加载图片EXIF信息
        loadExif: function (id) {

        },
        //删除回复
        deleteComment: function (ele, id) {
            var _this = this,
                currentId = _this.pic_handle.val()
                Wind.Util.ajaxConfirm({
                    elem: ele,
                    href: ele.attr('href'),
                    msg: "确定要删除这条评论吗?",
                    callback: function () {
                        Wind.Util.resultTip({
                            msg: '删除成功'
                        });
                        _this.loadComment(currentId)
                    }
                });
        },
        //举报
        report:function(){
        	console.log(9)
        	var _this = this,
        		$report=$("#report");
        	$report.click(function(e){
        		e.preventDefault();
        	})	
        }
    }
    slider.init();
})()