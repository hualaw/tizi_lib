define(function(require, exports) {
	require('cookies');
	require('tizi_ajax');
	require('tiziDialog');
	//错误信息弹框
	exports.force_download = function(url,fname,openbox,noxunlei){
		if(!noxunlei){
			url = url + '&session_id=' + $.cookies.get(baseSessID);
		}
		var ie_ver = this.ie_version();
		if(openbox == true || ie_ver==6.0 ||ie_ver==7.0 || ie_ver==8.0){
			if(fname == '' || fname == undefined) fname = "是否下载？";
			else fname = "是否下载《" + fname + "》？";
			$.tiziDialog({
				content: fname,
				ok:false,
				icon:null,
				button:[{
					name:'点击下载',
					href:url,
					className:'aui_state_highlight',
					target:'_self'
				}]
			});
			return false;
		}
		window.location.href=url;
	};
	exports.ie_version = function() {
		//var userAgent = window.navigator.userAgent.toLowerCase();
		var ie = $.browser.msie;
		var version = $.browser.version;
		if(ie) return version;
		else return false;
	};
});