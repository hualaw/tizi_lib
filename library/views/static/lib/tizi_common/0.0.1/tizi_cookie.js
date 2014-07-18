define(function(require, exports) {
	// require('ecSwfObject');
	// require('everCookie');
	// require('flashCookie');

	require.async(['ecSwfObject','cookies','everCookie','flashCookie'],function(){
		var ec = new evercookie({
			baseurl: staticBaseUrlName + staticVersion + 'lib/evercookie/0.4.0',
			asseturi: '/assets',
			phpuri: '/php'
		});

		var msg = null;
		var debug = true;

		var fc = new SwfStore({
			swf_url: staticBaseUrlName + staticVersion + 'lib/flashcookie/1.9.1/storage.swf',
			onready: function(){
				cookieCheck(true);
			},
			onerror: function(){
				cookieCheck(false);
			}
		});

		function cookieCheck(fcobj)
		{
			ec.get("uid", function(ecuid, all) {
				var fcuid = null;
				if(fcobj) {
					fcuid = fc.get("uid");
				}
				if(!fcuid && !ecuid){
					var fcuid = ecuid = $.cookies.get(baseSessID);
					ec.set("uid", fcuid);
					msg = "ecset:"+ecuid;
					if(fcobj) {
						fc.set("uid", fcuid);
						msg = msg + ";fcset:"+fcuid;
					} else {
						msg = msg + ";efc"
					}
				} else if(!fcuid && ecuid) {
					if(fcobj) {
						fcuid = ecuid;
						fc.set("uid", fcuid);
						msg = "fcset:"+fcuid;
					} else {
						msg = "uid:"+ecuid+";efc";
					}
				} else if(fcuid != ecuid) {
					ecuid = fcuid;
					ec.set("uid", ecuid);
					msg = "ecset:"+fcuid;
				} else {
					msg = "uid:"+ecuid;
				}
				if(debug && msg) {
					document.cookie = "cookie_debug="+msg;
					$('.footer').append(msg);
					//alert(msg)
				}
			}, 0);
		}
		
	});

	// $.ajax({
	// 	type:"GET",
	// 	url:"http://lk.brand.sogou.com/svc/getyyid.php",
	// 	dataType:"jsonp",
	// 	callback:"idcb",
	// 	success:function(){},
	// 	error:function(){}
	// })
	
});