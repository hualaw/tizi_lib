;(function($){
	$.tizi_callback = function(data,success){
		if(data == undefined) return;
		if(data.login === false){
			if(typeof seajs == 'object'){
				// 加载公共登录框
				seajs.use('tizi_login_form',function(ex){
					ex.loginForm(data.html,data.redirect);
				});
			}else{
				window.location.href = baseUrlName;
			}
			return false;
		}else if(data.dnlogin === true){
			$.tiziDialog({
				content:data.error,
				close:function(){
					window.location.href=data.redirect;
				}
			});
			return false;
		}else if(data.auth === true){
			if(typeof seajs == 'object'){
				// 加载公共登录框
				seajs.use('tizi_auth_form',function(ex){
					ex.authForm(data.html);
				});
			}else{
				window.location.href = baseUrlName;
			}
			return false;
		}else if(data.token === false){
			window.location.reload();
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
			var len = options_data.length;
			options_data[len] = {'name':'ver','value':(new Date).valueOf()};
			if(type.toLocaleLowerCase() == 'post'){
				options_data[len+1] = {'name':'token','value':basePageToken};
				options_data[len+2] = {'name':'page_name','value':basePageName};
			}
			if(callback_name){
				var len = options_data.length;
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
		
		var callback_name='';
		if(options['dataType']=='jsonp'){
			callback_name='callback';
		}else{			
			options['dataType']='json';
		}
		options['data'] = $.tizi_token(options['data'],options['type'],serialize,callback_name);
		var success=options['success'];
		options['success']=function(data){$.tizi_callback(data,success);}

		$.ajax(options);
	}
})(jQuery);