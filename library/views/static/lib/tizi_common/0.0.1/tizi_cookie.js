define(function(require, exports) {
	
	var ec = new evercookie({
		baseurl: staticBaseUrlName + staticVersion + 'lib/evercookie/0.4.0',
		asseturi: '/assets',
		phpuri: '/php'
	});

	var fcuid = null;

	var fc = new SwfStore({
		swf_url: staticBaseUrlName + staticVersion + 'lib/flashcookie/1.9.1/storage.swf',
		debug: true,
		onready: function(){
			fcuid = fc.get("uid");
		},
		onerror: function(){
		}
	});

	ec.get("uid", function(ecuid, all) {
		if(!fcuid) {
			if(typeof fc == 'object') {
				fc.set("uid", ecuid);
				alert("fcset:"+ecuid);
			}
		} else if(fcuid != ecuid) {
			ec.set("uid", fcuid);
			alert("ecset:"+fcuid);
		} else {
			alert("uid:"+ecuid);
		}
	}, 0);

});