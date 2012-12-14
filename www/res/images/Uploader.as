package  {
	
	import flash.display.Sprite;
	import flash.events.DataEvent;
	import flash.events.Event;
	import flash.events.HTTPStatusEvent;
	import flash.events.IOErrorEvent;
	import flash.events.MouseEvent;
	import flash.events.ProgressEvent;
	import flash.events.SecurityErrorEvent;
	import flash.external.ExternalInterface;
	import flash.net.FileFilter;
	import flash.net.FileReference;
	import flash.net.FileReferenceList;
	import flash.net.URLLoader;
	import flash.net.URLRequest;
	import flash.net.URLRequestMethod;
	import flash.net.URLVariables;
	import flash.system.Security;
	
	public class Uploader extends Sprite
	{
		private var url:String;
		private var restCount:Number;
		private var isUploading:Boolean = false;
		private var _filelist:Array = new Array();
		private var browser:FileReferenceList = new FileReferenceList();
		private var maxFilesize:Object={};
		private var fileTypes:Array;
		private var fileTypeStr:String;
		private var postData:URLVariables = new URLVariables();
		private var currentId:uint=0;
		private var albumId:uint=0;
		private var fileid:uint = 0;
		private var jsobject:String;
		public function Uploader() {
			//Security.loadPolicyFile();
			Security.allowDomain("*");
			btn.useHandCursor = true;
			//upto.useHandCursor = true;
			url = this.loaderInfo.parameters["url"];
			jsobject = this.loaderInfo.parameters["jsobject"];
			ExternalInterface.addCallback("remove", removeUpload);
			ExternalInterface.addCallback("setAlbumId", setAlbumId);
			ExternalInterface.addCallback("setDesc", setDesc);
			ExternalInterface.addCallback('setFileType', setFileType);
			ExternalInterface.addCallback('setPostData', setPostData);
			ExternalInterface.addCallback('beginUpload', beginUpload);
			/**
			 * 绑定点击事件
			 */
			btn.addEventListener(MouseEvent.CLICK,selectFiles);
			//upto.addEventListener(MouseEvent.CLICK,beginUpload);
			
			browser.addEventListener(Event.SELECT, selectHandler);
			browser.addEventListener(Event.CANCEL, cancelHandler);
			ExternalInterface.call(jsobject+'.initflash');
			
		}
		private function setPostData(dat:Object):void
		{
			for(var p:String in dat){
				postData[p]=dat[p];
			}
		}
		/**
		 * 设定文件类型
		 */
		public function setFileType(cfg:Object):void
		{
			var _types:Array=new Array();
			maxFilesize = new Object();
			for(var p:String in cfg){
				
				_types.push('*.'+p);
				maxFilesize[p] = parseInt(cfg[p]) * 1024;
			}
			fileTypeStr=_types.join(',');
			fileTypes = [new FileFilter("所有支持的格式("+fileTypeStr+")", _types.join(';'))];
		}
		/**
		 * 转换列表给js调用
		 */
		private function get queue():Array
		{
			return _filelist.map(setQueue)
		}
		private function setQueue(n:Object,i:int, a:Array):Object
		{
			return {'name':n.file.name,'size':n.file.size,'error':n.error,'desc':n.desc, 'fileid':n.fileid};
		}
		/**
		 * 开始选择文件
		 **/
		private function selectFiles(event:MouseEvent):void
		{
			try
			{
				browser.browse(fileTypes);
			}
			catch (error:Error)
			{
				ExternalInterface.call('alert',"无法打开文件夹.");
			}
		}
		/**
		 * 选择完毕
		 **/
		private function selectHandler(event:Event):void
		{
			var filelist:Array = browser.fileList;
			var i:uint = 0;
			while (i < filelist.length)
			{
				var ext:String = filelist[i].name.substr((filelist[i].name.lastIndexOf(".") + 1)).toLowerCase();
				var status:String='';
				if (typeof(maxFilesize[ext]) == undefined||fileTypeStr.indexOf(ext)<0)
				{
					status = "exterror";
				}
				else if (filelist[i].size > maxFilesize[ext])
				{
					status = "toobig";
				}
				_filelist.push({file:filelist[i], error:status, desc:null, fileid: fileid++});
				i++;
			}
			ExternalInterface.call(jsobject + ".list", queue);
			return;
		}
		/**
		 * 取消选择
		 **/
		private function cancelHandler(event:Event):void
		{
			
		}
		/**
		 * 删除
		 */
		private function removeUpload(fileid:uint):void
		{
			var start:uint = 0, l:uint = _filelist.length;
			while(start < l){
				if(_filelist[start].fileid == fileid){
					break;
				}
				start++;
			}
			_filelist.splice(start,1);
		}

		/**
		 * 单个上传
		 */
		private function upload(item:FileReference,desc:String):void
		{
			if (item != null)
			{
				item.addEventListener(ProgressEvent.PROGRESS, fileProgressHandler);
				item.addEventListener(IOErrorEvent.IO_ERROR, ioErrorHandler);
				item.addEventListener(SecurityErrorEvent.SECURITY_ERROR, securityErrorHandler);
				item.addEventListener(HTTPStatusEvent.HTTP_STATUS, httpErrorHandler);
				item.addEventListener(Event.COMPLETE, completeHandler);
				item.addEventListener(DataEvent.UPLOAD_COMPLETE_DATA, dataHandler);
				var request:URLRequest = new URLRequest(url + "&photoid=" + currentId + "&aid=" + albumId + "&t=" + new Date().getTime());
				postData.desc = desc;
				
				request.data = postData;
				request.method = URLRequestMethod.POST;
				item.upload(request);
			}
		}
		private function dataHandler(event:DataEvent)
		{
			//删除当前的
			var _fileId:int = _filelist[currentId].fileid,
				_name:String = _filelist[currentId].file.name,
				_size:int = _filelist[currentId].file.size,
				_val:String = event.data;
			_filelist.splice(currentId, 1);
			ExternalInterface.call(jsobject + ".complete", _fileId, _val, _name, _size);
			//开始下一个
			uploadNext();
			/*ExternalInterface.call(jsobject + ".error", );*/
		}
		/**
		 * 进度控制
		 */
		private function fileProgressHandler(event:ProgressEvent):void
		{
			var currentFile:Object = _filelist[currentId];
			ExternalInterface.call(jsobject + ".progress",currentFile.fileid,Math.floor(100*event.bytesLoaded/currentFile.file.size));
		}
		/**
		 * 开始上传
		 */
		public function beginUpload():void
		{
			var str:String = ExternalInterface.call(jsobject + ".getRestCount") as String;
			restCount = (str=='Infinity')?Number.POSITIVE_INFINITY:ExternalInterface.call(jsobject + ".getRestCount");
			if(!isUploading)
			{
				isUploading = true;
				currentId=0;
				uploadNext();
			}
		}
		private function uploadNext()
		{
			while(currentId<_filelist.length && restCount>0)
			{
				if(!_filelist[currentId] || _filelist[currentId].error)
					currentId++;
				else{
					restCount--;
					var desc:String = _filelist[currentId].desc === null?_filelist[currentId].file.name:_filelist[currentId].desc;
					upload(_filelist[currentId].file, desc);
					return;
				}
			}
			if(currentId >= _filelist.length || restCount==0)
			{
				isUploading = false;
				return;
			}
		}
		/**
		 * 上传完毕
		 */
		private function completeHandler(event:Event):void
		{
			/*isSuccess = true;
			//删除当前的
			var _fileId:int = _filelist[currentId].fileid;
			_filelist.splice(currentId,1);
			
			ExternalInterface.call(jsobject + ".complete", _fileId);
			//开始下一个
			uploadNext();*/
		}
		/**
		 * I/O错误
		 */
		private function ioErrorHandler(event:IOErrorEvent):void
		{
			ExternalInterface.call(jsobject + ".error",'I/O Error:'+event.toString());
			isUploading = false;
		}
		/**
		 * 安全性错误
		 */
		private function securityErrorHandler(event:SecurityErrorEvent):void
		{
			ExternalInterface.call(jsobject + ".error", 'Security Error:' + event.toString() );
			isUploading = false;
		}
		/**
		 * HTTP错误
		 */
		private function httpErrorHandler(event:HTTPStatusEvent):void
		{
			switch (event.status) {
				case 200:
					break;
				case 404:
					ExternalInterface.call(jsobject + ".error", 'HTTP Error:' + event.status.toString() );
					isUploading = false;
					break;
				default:
					ExternalInterface.call(jsobject + ".error", 'HTTP Error:' + event.status.toString() );
					isUploading = false;
					break;
			}
		}
		/**
		 * 设置相册Id
		 */
		public function setAlbumId(aid:uint):void
		{
			albumId = aid;
		}
		/**
		 * 存储描述
		 */
		public function setDesc(id:uint,desc:String):void
		{
			_filelist[id].desc = desc;
		}
	}
	
}
