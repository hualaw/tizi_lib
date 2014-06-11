define(function(require,exports){
	exports.player = function(_json){
		// 先判断是否支持flash
		var noflash = $('#noflashHtml').html();
		seajs.use('tizi_validform',function(ex){
			ex.detectFlashSupport(function(){
		    	$('.mp3player').html(noflash);
		    });
		});
		// 请求播放插件
		require('swfObject');
		var so = new SWFObject(staticPath + "lib/tizi_static/image/common/audioplayer.swf","player","200","24","9","#ffffff");
		so.addParam("allowfullscreen","true");
		so.addParam("allowscriptaccess","always");
		so.addParam("wmode","opaque");
		so.addParam("quality","high");
		so.addParam("salign","lt");
		so.addVariable("soundFile",_json.mp3Address);　
		so.write(_json.id);
	}
	
});