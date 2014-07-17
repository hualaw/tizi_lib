define(function(require, exports) {
	require('ecSwfObject');
	require('everCookie');
	require('flashCookie');

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
		if(!fcuid) {
			if(typeof fc == 'object') {
				fc.set("uid", ecuid);
				document.cookie = "cookie_debug=fcset:"+ecuid;
			}
		} else if(fcuid != ecuid) {
			ec.set("uid", fcuid);
			document.cookie = "cookie_debug=ecset:"+fcuid;
		} else {
			document.cookie = "cookie_debug=uid:"+ecuid;
		}
	}, 0);
});