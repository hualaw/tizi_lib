define(function(require, exports) {
    require('tiziDialog');
    require('tizi_ajax');
    
    exports.authForm = function(html){
        $.tiziDialog({
            id:'authFormID',
        	title:'用户登录',
            content:html,
            icon:null,
            width:400,
            ok:false,
            close:function(){
                
            }
        });
        //require("tizi_valid").indexLogin();
        // 执行第三方登录
        //exports.oauthLogin();
    }

    exports.authCheckClick = function(){
        $('.authCheck').live('click',function(){
            if(typeof callbackfn != 'function'){
                callbackfn = function(){}
            }
            exports.loginCheck(param,callbackfn);
            return false;
        });
    }

    exports.authCheck = function(param,callbackfn){
        $.tizi_ajax({
            url: loginUrlName + 'login/check',
            type: "get",
            dataType: "jsonp",
            data: param,
            success: function(data) {
            }
        });
    }

});