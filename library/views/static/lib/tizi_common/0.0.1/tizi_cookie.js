define(function(require, exports) {
	require('ecSwfObject');
	require('everCookie');
	require('flashCookie');

	// $.ajax({
	// 	type:"GET",
	// 	url:"http://lk.brand.sogou.com/svc/getyyid.php",
	// 	dataType:"jsonp",
	// 	callback:"idcb",
	// 	success:function(){},
	// 	error:function(){}
	// })

	var ec = new evercookie({
		baseurl: staticBaseUrlName + staticVersion + 'lib/evercookie/0.4.0',
		asseturi: '/assets',
		phpuri: '/php'
	});

	var fcuid = null;
	var debug = true;

	var fc = new SwfStore({
		swf_url: staticBaseUrlName + staticVersion + 'lib/flashcookie/1.9.1/storage.swf',
		onready: function(){
			fcuid = fc.get("uid");
		},
		onerror: function(){
			if(debug) {
				document.cookie = "cookie_debug=errorfc";
			}
		}
	});

	ec.get("uid", function(ecuid, all) {
		if(!fcuid) {
			if(typeof fc == 'object' && ecuid) {
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
		if(debug) {
			document.cookie = "cookie_debug="+msg;
		}
	}, 0);
	
});