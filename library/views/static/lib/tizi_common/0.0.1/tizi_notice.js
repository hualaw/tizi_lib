define(function(require, exports) {
	require('tizi_ajax');
	exports.getNotice = function(){
		return false;
		$.tizi_ajax({
			type:'GET',
			url:loginUrlName + 'notice',
			dataType:"jsonp",
			success:function(data){
				if(data.status==99){		
					if(data.msg > 0){
						//$('#notification').attr('class','newMassage').html('新消息('+data.msg+')');
					}else{
						//$('#notification').attr('class','normalMassage').html('我的消息');
					}
				};
				/*
				setTimeout(function(){
					seajs.use('tizi_notice', function(ex){
						ex.getNotice();
					});
				}, 12000);
				*/
			}
		});
	};

	//学堂 消息
	exports.xGetNotice = function (){
		$.tizi_ajax({
	        type:'GET',
	        url:loginUrlName + 'notice',
	        dataType:"jsonp",
	        success:function(data){
	            if(data.status==99){
	                if(data.msg > 0){
	                    $('.warnBox').html('<var>'+data.msg+'</var>');
	                }else{
	                    $('.warnBox').html('');
	                }
	            };
	        }
	    });
	}
});
