 /*!
 * HeadJS     The only script in your <HEAD>    
 * Author     Tero Piirainen  (tipiirai)
 * Maintainer Robert Hoffmann (itechnology)
 * License    MIT / http://bit.ly/mit-license
 *
 * Version 0.99
 * http://headjs.com
 */
 /* modify : head ==> Wind */
;(function(i,l){var J=i.document,j=[],x=[],v={},E={},o="async" in J.createElement("script")||"MozAppearance" in J.documentElement.style||i.opera,s,f,b=i.head_conf&&i.head_conf.head||"Wind",t=i[b]=(i[b]||function(){t.ready.apply(null,arguments)}),H=1,z=2,D=3,k=4;if(o){t.load=function(){var K=arguments,L=K[K.length-1],e={};if(!a(L)){L=null}g(K,function(N,M){if(N!==L){N=I(N);e[N.name]=N;n(N,L&&M===K.length-2?function(){if(C(e)){w(L)}}:null)}});return t}}else{t.load=function(){var e=arguments,L=[].slice.call(e,1),K=L[0];if(!s){x.push(function(){t.load.apply(null,e)});return t}if(!!K){g(L,function(M){if(!a(M)){y(I(M))}});n(I(e[0]),a(K)?K:function(){t.load.apply(null,L)})}else{n(I(e[0]))}return t}}t.js=t.load;t.test=function(O,L,e,N){var K=(typeof O==="object")?O:{test:O,success:!!L?r(L)?L:[L]:false,failure:!!e?r(e)?e:[e]:false,callback:N||h};var M=!!K.test;if(M&&!!K.success){K.success.push(K.callback);t.load.apply(null,K.success)}else{if(!M&&!!K.failure){K.failure.push(K.callback);t.load.apply(null,K.failure)}else{N()}}return t};t.ready=function(K,M){if(K===J){if(f){w(M)}else{j.push(M)}return t}if(a(K)){M=K;K="ALL"}if(typeof K!=="string"||!a(M)){return t}var L=E[K];if(L&&L.state===k||K==="ALL"&&C()&&f){w(M);return t}var e=v[K];if(!e){e=v[K]=[M]}else{e.push(M)}return t};t.ready(J,function(){if(C()){g(v.ALL,function(e){w(e)})}if(t.feature){t.feature("domloaded",true)}});function h(){}function g(e,M){if(!e){return}if(typeof e==="object"){e=[].slice.call(e)}for(var L=0,K=e.length;L<K;L++){M.call(e,e[L],L)}}function m(e,K){var L=Object.prototype.toString.call(K).slice(8,-1);return K!==l&&K!==null&&L===e}function a(e){return m("Function",e)}function r(e){return m("Array",e)}function B(L){var e=L.split("/"),K=e[e.length-1],M=K.indexOf("?");return M!==-1?K.substring(0,M):K}function w(e){e=e||h;if(e._done){return}e();e._done=1}function I(M){var K={};if(typeof M==="object"){for(var e in M){if(!!M[e]){K={name:e,url:M[e]}}}}else{K={name:B(M),url:M}}var L=E[K.name];if(L&&L.url===K.url){return L}E[K.name]=K;return K}function C(e){e=e||E;for(var K in e){if(e.hasOwnProperty(K)&&e[K].state!==k){return false}}return true}function G(e){e.state=z;g(e.onpreload,function(K){K.call()})}function y(e,K){if(e.state===l){e.state=H;e.onpreload=[];c({url:e.url,type:"cache"},function(){G(e)})}}function n(e,K){K=K||h;if(e.state===k){K();return}if(e.state===D){t.ready(e.name,K);return}if(e.state===H){e.onpreload.push(function(){n(e,K)});return}e.state=D;c(e,function(){e.state=k;K();g(v[e.name],function(L){w(L)});if(f&&C()){g(v.ALL,function(L){w(L)})}})}function c(L,O){O=O||h;var M;if(/\.css[^\.]*$/.test(L.url)){M=J.createElement("link");M.type="text/"+(L.type||"css");M.rel="stylesheet";M.href=L.url}else{M=J.createElement("script");M.type="text/"+(L.type||"javascript");M.src=L.url}M.onload=M.onreadystatechange=N;M.onerror=e;M.async=false;M.defer=false;function e(P){P=P||i.event;M.onload=M.onreadystatechange=M.onerror=null;O()}function N(P){P=P||i.event;if(P.type==="load"||(/loaded|complete/.test(M.readyState)&&(!J.documentMode||J.documentMode<9))){M.onload=M.onreadystatechange=M.onerror=null;O()}}var K=J.head||J.getElementsByTagName("head")[0];K.insertBefore(M,K.lastChild)}function p(){if(!J.body){i.clearTimeout(t.readyTimeout);t.readyTimeout=i.setTimeout(p,50);return}if(!f){f=true;g(j,function(e){w(e)})}}function q(){if(J.addEventListener){J.removeEventListener("DOMContentLoaded",q,false);p()}else{if(J.readyState==="complete"){J.detachEvent("onreadystatechange",q);p()}}}if(J.readyState==="complete"){p()}else{if(J.addEventListener){J.addEventListener("DOMContentLoaded",q,false);i.addEventListener("load",p,false)}else{J.attachEvent("onreadystatechange",q);i.attachEvent("onload",p);var u=false;try{u=i.frameElement==null&&J.documentElement}catch(F){}if(u&&u.doScroll){(function d(){if(!f){try{u.doScroll("left")}catch(e){i.clearTimeout(t.readyTimeout);t.readyTimeout=i.setTimeout(d,50);return}p()}})()}}}setTimeout(function(){s=true;g(x,function(e){e()})},300);var A=navigator.userAgent.toLowerCase();A=/(webkit)[ \/]([\w.]+)/.exec(A)||/(opera)(?:.*version)?[ \/]([\w.]+)/.exec(A)||/(msie) ([\w.]+)/.exec(A)||!/compatible/.test(A)&&/(mozilla)(?:.*? rv:([\w.]+))?/.exec(A)||[];if(A[1]=="msie"){A[1]="ie";A[2]=document.documentMode||A[2]}t.browser={version:A[2]};t.browser[A[1]]=true;if(t.browser.ie){g("abbr|article|aside|audio|canvas|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video".split("|"),function(e){J.createElement(e)})}})(window);

/*********Wind JS*********/
/*
 * PHPWind JS core
 * @Copyright   : Copyright 2011, phpwind.com
 * @Descript    : PHPWind核心JS
 * @Author      : chaoren1641@gmail.com
 * @Thanks      : head.js (http://headjs.com)
 * $Id: wind.js 21129 2012-11-28 10:28:34Z hao.lin $            :
 */


/*
 * 防止浏览器不支持console报错
 */
if(!window.console) {
    window.console = {};
    var funs = ["profiles", "memory", "_commandLineAPI", "debug", "error", "info", "log", "warn", "dir", "dirxml", "trace", "assert", "count", "markTimeline", "profile", "profileEnd", "time", "timeEnd", "timeStamp", "group", "groupCollapsed", "groupEnd"];
    for(var i = 0;i < funs.length; i++) {
        console[funs[i]] = function() {};
    }
}

/*
*解决ie6下不支持背景缓存
*/
Wind.ready(function() {
	if (!+'\v1' && !('maxHeight' in document.body.style)) {
		try{
			document.execCommand("BackgroundImageCache", false, true);
		}catch(e){}
	}
});

/*
*wind core
*/
;(function(win) {
	var root = win.GV.JS_ROOT || location.origin + '/js/dev/', //在core.js加载之前定义GV.JS_ROOT
		ver = win.GV.JS_VERSION || '9.0beta',
		//定义常用JS组件别名，使用别名加载
		alias = {
            datePicker         : 'ui_libs/datePicker/datePicker',
	    	dialog             : 'ui_libs/dialog/dialog',
            dragSort           : 'ui_libs/dragSort/dragSort',
            chosen             : 'ui_libs/chosen/chosen',
            colorPicker        : 'ui_libs/colorPicker/colorPicker',
            global             : 'pages/common/global',
            jquery             : 'jquery',
            region             : 'ui_libs/region/region',
            school             : 'ui_libs/school/school',
            tabs               : 'ui_libs/tabs/tabs',

            //jquery util plugs
            ajaxForm          : 'util_libs/ajaxForm',
            bgiframe          : 'util_libs/bgiframe',
            dateSelect        : 'util_libs/dateSelect',
            draggable         : 'util_libs/draggable',
            dragsort          : 'util_libs/dragsort',
            dragUpload        : 'util_libs/dragUpload',
            emailAutoMatch    : 'util_libs/emailAutoMatch',
            gallerySlide      : 'util_libs/gallerySlide',
            hotkeys           : 'util_libs/hotkeys',
            hoverdelay        : 'util_libs/hoverdelay',
            lazyload          : 'util_libs/lazyload',
            lazySlide         : 'util_libs/lazySlide',
            localStorage      : 'util_libs/localStorage',
            rangeInsert       : 'util_libs/rangeInsert',
            requestFullScreen : 'util_libs/requestFullScreen',
            scrollFixed       : 'util_libs/scrollFixed',
            slides            : 'util_libs/slides',
            slidePlayer       : 'util_libs/slidePlayer',
            timeago           : 'util_libs/timeago',
            tablesorter       : 'util_libs/tablesorter',
            textCopy          : 'util_libs/textCopy/textCopy',
            uploadPreview     : 'util_libs/uploadPreview',
            validate          : 'util_libs/validate',

			//windeditor
			windeditor		  : 'windeditor/windeditor',

			//native js util plugs
			swfupload         : 'util_libs/swfupload/swfupload'
		},
		alias_css = {
			colorPicker	: 'ui_libs/colorPicker/style',
			datePicker	: 'ui_libs/datePicker/style',
			chosen		: 'ui_libs/chosen/chosen'
		};

	//add suffix and version
	for(var i in alias) {
		if (alias.hasOwnProperty(i)) {
			alias[i] = root + alias[i] +'.js?v=' + ver;
		}
	}

	for(var i in alias_css) {
		if (alias_css.hasOwnProperty(i)) {
			alias_css[i] = root + alias_css[i] +'.css?v=' + ver;
		}
	}

	//css loader
	win.Wind = win.Wind || {};
    //!TODO old webkit and old firefox does not support
	Wind.css = function(alias/*alias or path*/,callback) {
		var url = alias_css[alias] ? alias_css[alias] : alias
		var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        link.onload = link.onreadystatechange = function() {//chrome link无onload事件
            var state = link.readyState;
            if (callback && !callback.done && (!state || /loaded|complete/.test(state))) {
                callback.done = true;
                callback();
            }
        }
        document.getElementsByTagName('head')[0].appendChild(link);
	};

	//Using the alias to load the script file
	Wind.use = function() {
		var args = arguments,len = args.length;
        for( var i = 0;i < len;i++ ) {
        	if(typeof args[i] === 'string' && alias[args[i]]) {
        		args[i] = alias[args[i]];
        	}
        }
		Wind.js.apply(null,args);
	};

    //Wind javascript template (author: John Resig http://ejohn.org/blog/javascript-micro-templating/)
    var cache = {};
    Wind.tmpl = function (str, data) {
        var fn = !/\W/.test(str) ? cache[str] = cache[str] || tmpl(str) :
        new Function("obj", "var p=[],print=function(){p.push.apply(p,arguments);};" +
        "with(obj){p.push('" +
        str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');}return p.join('');");
        return data ? fn(data) : fn;
    };
    //Wind全局功能函数命名空间
    Wind.Util = {}
})(this);

