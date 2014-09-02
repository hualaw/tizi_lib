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
        //require("tizi_valid").indexAuth();
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
            url: loginUrlName + 'login/auth_check',
            type: "get",
            dataType: "jsonp",
            data: param,
            success: function(data) {
                if(data.errorcode){
                    if(typeof callbackfn == 'function'){
                        callbackfn();
                    }
                }else{
                    exports.authForm(data.html);
                    seajs.use('placeHolder');
                }
            }
        });
    }

});