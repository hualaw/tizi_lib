define(function(require, exports) {
	require('cookies');
	require('tizi_ajax');
	require('tiziDialog');
	//错误信息弹框
	exports.force_download = function(url,fname,openbox,noxunlei){
		if(!noxunlei){
			url = url + '&session_id=' + $.cookies.get(baseSessID);
		}
		var ie_ver = exports.ie_version();
		if(openbox == true || ie_ver==6.0 ||ie_ver==7.0 || ie_ver==8.0){
			if(fname == '' || fname == undefined) fname = "请点击下载";
			else fname = "请点击下载《" + fname + "》";
			$.tiziDialog({
				content: fname,
				ok:false,
				cancel:false,
				dblclick:false,
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

	/*是否下载xxx文件*/
	/* no_tizi 为true，说明是从其他项目来的请求，要做成jsonp提交 */
	exports.down_confirm_box = function(url,fname,noxunlei,share_id,no_tizi){
		if(!noxunlei){
			url = url + '&session_id=' + $.cookies.get(baseSessID);
		}
		$.tiziDialog({
			content: '是否下载文件《'+fname+'》？',
			ok:false,
			cancel:true,
			icon:null,
			button:[{
				name:'点击下载',
				href:url,
				className:'aui_state_highlight clickDown',
				target:'_self'
			}]
		});
		if(share_id!=undefined){
			$('.clickDown').click(function(){
				json = 'json';

				if(no_tizi != undefined){
					json = 'jsonp';
				} 
				$.tizi_ajax({
                    url: tiziUrlName + "resource/cloud_base/add_download_count", 
                    type: 'POST',
                    data: {'share_id':share_id},
                    dataType: json,
                    success: function(data){  }
		        }); 
	        });
		}
	};
});
