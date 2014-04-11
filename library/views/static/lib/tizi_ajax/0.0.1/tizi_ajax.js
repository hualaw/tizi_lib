;(function($){
	$.tizi_callback = function(data,success){
		if(data == undefined) return;
		if(data.login === false){
			// 加载公共登陆框
			seajs.use('tizi_login_form',function(ex){
				ex.loginForm(data.html);
			});
			return false;
		}else if(data.token === false){
			window.location.reload();
			return false;
		}else if(data.reload === true){
			if(data.error){
				$.tiziDialog({
					content:data.error,
					close:function(){
						if(data.redirect){
							window.location.href=data.redirect;
						}else{
							window.location.reload();
						}
					}
				});
			}else{
				if(data.redirect){
					window.location.href=data.redirect;
				}else{
					window.location.reload();
				}
			}
			return false;
		}else{
			if(data.token != '' && data.token != undefined) {
				basePageToken = data.token;
			}

			if(success == undefined) success = function(data){};
			success(data);
		}
	}
	$.tizi_token = function(options_data,type,serialize,callback_name){
		if(serialize === true){
			var len = options_data.length
			options_data[len] = {'name':'ver','value':(new Date).valueOf()};
			if(type.toLocaleLowerCase() == 'post'){
				options_data[len+1] = {'name':'token','value':basePageToken};
				options_data[len+2] = {'name':'page_name','value':basePageName};
			}
			if(callback_name){
				var len = options_data.length
				options_data[len] = {'name':'callback_name','value':callback_name};
			}
		}else{
			options_data['ver']=(new Date).valueOf();
			if(type.toLocaleLowerCase() == 'post'){
				options_data['token']=basePageToken;
				options_data['page_name']=basePageName;
			}
			if(callback_name){
				options_data['callback_name']=callback_name;
			}
		}
		return options_data;
	}
	$.tizi_ajax = function(options,serialize){  
		var defaults = {  
		    url: "",  
		    type: "POST",
		    data: {},
		   	dataType: "json",
		   	async: true,
		   	success: function(){},
		   	error: function(){}
		};
		var options = $.extend(defaults, options);
		
		if(options['dataType']=='jsonp'){
			options['jsonp']='callback';
			options['data'] = $.tizi_token(options['data'],options['type'],serialize,options['jsonp']);

			var success=options['success'];
			callback=function(data){$.tizi_callback(data,success);}
			options['success']=function(){};
			options['error']=function(){};
		}else{			
			options['dataType']='json';
			options['data'] = $.tizi_token(options['data'],options['type'],serialize);
			
			var success=options['success'];
			options['success']=function(data){$.tizi_callback(data,success);}
		}

		$.ajax(options);
	}
})(jQuery);