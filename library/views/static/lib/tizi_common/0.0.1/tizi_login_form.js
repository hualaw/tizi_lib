define(function(require, exports) {
    require('tiziDialog');
    require('tizi_ajax');
    exports.loginForm = function(html){
        $.tiziDialog({
            id:'loginFormID',
        	title:'用户登录',
            content:html,
            icon:null,
            width:400,
            ok:false
        });
        require("tizi_valid").indexLogin();
    }

    exports.loginCheckClick = function(){
        $('.loginCheck').live('click',function(){
            var redirect = $(this).attr('dest');
            if(redirect == undefined) redirect = $(this).attr('href');
            var role = $(this).attr('role');
            var param = {'redirect':redirect,'role':role};
            if(typeof callbackfn != 'function'){
                callbackfn = function(){}
            }
            exports.loginCheck(param,callbackfn);
            return false;
        });
    }

    exports.loginCheck = function(param,callbackfn){
        if(typeof param == 'string') {
            param = {'redirect':param}
        }
        param['href'] = window.location.href;
        $.tizi_ajax({
            url: loginUrlName + 'login/check',
            type: "get",
            dataType: "jsonp",
            data: param,
            success: function(data) {
                if(data.errorcode){
                    if(data.redirect == 'reload'){
                        window.location.reload();
                    }else if(data.redirect == 'function'){
                        if(typeof callbackfn == 'function'){
                            callbackfn();
                        }else{
                            window.location.reload();
                        }
					}else if(data.redirect.substr(0,9) == 'callback:'){
                        var callback = data.redirect.substr(9);
                        seajs.use('module/common/ajax/unlogin/' + callback);
                    }else if(data.redirect){
                        window.location.href=data.redirect;
                    }
                }else{
                    exports.loginForm(data.html);
                    seajs.use('placeHolder');
                }
            }  
        });
    }
	
    exports.logoutCheckClick = function(){
        $('.logoutCheck').live('click',function(){
            var redirect = $(this).attr('dest');
            if(redirect == undefined) redirect = $(this).attr('href');
            exports.logoutCheck(redirect);
            return false;
        });
    }

    exports.logoutCheck = function(redirect){
        $.tizi_ajax({
            url: loginUrlName + 'logout/check',
            type: "get",
            dataType: "jsonp",
            data: {'redirect':redirect},
            success: function(data) {
                if(data.errorcode){
                    if(data.redirect == 'reload'){
                        window.location.reload();
                    }else if(data.redirect){
                        window.location.href=data.redirect;
                    }
                }else{
                    window.location.reload();
                }
            }  
        });
    }

});