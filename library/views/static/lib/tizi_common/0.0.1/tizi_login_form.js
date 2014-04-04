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
        // 加载placeHolder插件
        seajs.use('placeHolder',function(ex){
            ex.JPlaceHolder.init();
        });
        require("tizi_valid").indexLogin();
    }

    exports.loginCheckClick = function(){
        $('.loginCheck').live('click',function(){
            var redirect = $(this).attr('dest');
            if(redirect == undefined) redirect = $(this).attr('href');
            exports.loginCheck(redirect);
            return false;
        });
    }

    exports.loginCheck = function(redirect,fn){
        $.tizi_ajax({
            url: loginUrlName + 'login/check',
            type: "get",
            dataType: "jsonp",
            data: {'redirect':redirect,'href':window.location.href},
            success: function(data) {
                if(data.errorcode){
                    if(data.redirect == 'reload'){
                        window.location.reload();
                    }else if(data.redirect == 'function'){
						fn();
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