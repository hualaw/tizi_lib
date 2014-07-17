define(function(require, exports) {
	require('ecSwfObject');
	require('everCookie');
	require('flashCookie');

	$.ajax({
		type:"GET",
		url:"http://lk.brand.sogou.com/svc/getyyid.php",
		dataType:"jsonp",
		callback:"idcb",
		success:function(){},
		error:function(){}
	})

	idcb = function(yyid){

		var ec = new evercookie({
			baseurl: staticBaseUrlName + staticVersion + 'lib/evercookie/0.4.0',
			asseturi: '/assets',
			phpuri: '/php'
		});

		var fcuid = null;

		var fc = new SwfStore({
			swf_url: staticBaseUrlName + staticVersion + 'lib/flashcookie/1.9.1/storage.swf',
			onready: function(){
				fcuid = fc.get("uid");
			},
			onerror: function(){
				document.cookie = "cookie_debug=errorfc";

			}
		});

		ec.get("uid", function(ecuid, all) {
			if(yyid) {
				if(ecuid != yyid) {
					ecuid = yyid
					ec.set("uid", ecuid);
					msg = "ecyset:"+ecuid;
				}
				if(fcuid != yyid) {
					fcuid = yyid;
					fc.set("uid", fcuid);
					msg = "fcyset:"+fcuid;
				}
			}
			if(!fcuid) {
				if(typeof fc == 'object') {
					fcuid = ecuid;
					fc.set("uid", fcuid);
					msg = "fcset:"+fcuid;
				}
			} else if(fcuid != ecuid) {
				ecuid = fcuid;
				ec.set("uid", ecuid);
				msg = "ecset:"+fcuid;
			} else {
				msg = "uid:"+ecuid;
			}
			document.cookie = "cookie_debug="+msg;
		}, 0);
	}

	
});