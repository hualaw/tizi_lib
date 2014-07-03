define(function(require, exports) {
    require('tiziDialog');
    require('tizi_ajax');
    // 公共登录入口
    exports.init = function(){
        exports.loginCheckClick();
        exports.logoutCheckClick();
        exports.oauthLogin();
    };
    exports.loginForm = function(html,redirect){
        $.tiziDialog({
            id:'loginFormID',
        	title:'用户登录',
            content:html,
            icon:null,
            width:400,
            ok:false,
            close:function(){
                if(redirect.substr(0,9) == 'callback:'){
                    var callbackName = redirect.substr(9) + 'Close';
                    //seajs.use('module/common/ajax/loginForm/' + callback, function(ex){
                    //    ex.close();
                    //});
                    if(jQuery.isFunction( window[ callbackName ] )) {
                        window[ callbackName ]();
                    }
                }
            }
        });
        require("tizi_valid").indexLogin();
        // 执行第三方登录
        exports.oauthLogin();
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
                        var callbackName = data.redirect.substr(9) + 'Callback';
                        //seajs.use('module/common/ajax/loginForm/' + callback, function(ex){
                        //    ex.callback();
                        //});
                        if(jQuery.isFunction( window[ callbackName ] )) {
                            window[ callbackName ]();
                        }else{
                            window.location.reload();
                        }
                    }else if(data.redirect){
                        window.location.href=data.redirect;
                    }
                }else{
                    exports.loginForm(data.html,data.redirect);
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

    exports.checkLogin = function(loginfn,unloginfn){
        $.tizi_ajax({
            url: loginUrlName + 'login/check',
            type: "get",
            dataType: "jsonp",
            data: {'nohtml':1},
            success: function(data) {
                if(data.errorcode){
                    loginfn();
                }else{
                    unloginfn();
                }
            }  
        });
    }
    // 第三方登录
    exports.oauthLogin = function(){
        $('#oauthLogin a.qq').click(function(){
            var _url = $(this).attr('dUrl');
            if(baseIsMobile){
                window.location.href=_url;
            }else{
                window.open(_url,"TencentLogin","width=600,height=400,menubar=0,scrollbars=1,resizable=1,status=1,titlebar=0,toolbar=0,location=1");
            }
        });
        $('#oauthLogin a.weibo').click(function(){
            var _url = $(this).attr('dUrl');
            if(baseIsMobile){
                window.location.href=_url;
            }else{
                window.open(_url,"WeiboLogin","width=600,height=400,menubar=0,scrollbars=1,resizable=1,status=1,titlebar=0,toolbar=0,location=1");
            }
        });
    }
});