function TiZiavatar() {
	var id				= 'TiZiavatar'				//flash文件的ID
	var file			= staticUrlName + staticVersion + 'lib/TiZiavatar/TiZiavatar.swf';		//flash文件的路径
	var	version			= "10.1.0";					//播放该flash所需的最低版本
	var	expressInstall	= '/expressInstall.swf';	//expressInstall.swf的路径
	var	width			= 630;						//flash文件的宽度
	var	height			= 430;						//flash文件的高度
	var container		= id;						//装载flash文件的容器(如div)的id
	var flashvars		= {};
	var callback		= function(){};
	var heightChanged	= false;
	//智能获取参数，字符类型为装载flash文件的容器(如div)的id，第一个数字类型的为高度，第二个为宽度，第一个object类型的为参数对象，如此4个参数的顺序可随意。
	for(var i = 0; i < arguments.length; i++)
	{
		if(typeof arguments[i] == 'string')
		{
			container = arguments[i];
		}
		else if(typeof arguments[i] == 'number')
		{
			if(heightChanged)
			{
				width = arguments[i];
			}
			else
			{
				height = arguments[i];
				heightChanged = true;
			}
		}
		else if(typeof arguments[i] == 'function')
		{
			callback = arguments[i];
		}
		else
		{
			flashvars = arguments[i];
		}
	}
	var vars = {
		id : id,
		avatar_sizes_desc : "100*100像素|50*50像素|32*32像素",
        avatar_sizes    :   "100*100|50*50|32*32",
        tab_visible: false,//不显示选项卡，外部自定义
        button_visible: false,//不显示按钮，外部自定义
        checkbox_visible: false,//不显示复选框，外部自定义
        browse_box_align : "left",
        webcam_box_align : "left",
        src_upload: 0//是否上传原图片的选项：2-显示复选框由用户选择，0-不上传，1-上传
	};
	//合并参数
	for (var name in flashvars)
	{
		if(flashvars[name] != null)
		{
			vars[name] = flashvars[name];
		}
	}
	var params = {
		menu				: 'true',
		scale				: 'noScale',
		allowFullscreen		: 'true',
		allowScriptAccess	: 'always',
		wmode				: 'transparent'
	};
	var attributes = {
		id	: vars.id,
		name: vars.id
	};
	var swf = null;
	var	callbackFn = function (e) {
		swf = e.ref;
		//tizi add swf eq null condition
		if(swf) swf.eventHandler = function(json){
			callback.call(swf, json);
		};
	};
	swfobject.embedSWF(
		file, 
		container,
		width,
		height,
		version,
		expressInstall,
		vars,
		params, 
		attributes,
		callbackFn
	);
	return swf;
}