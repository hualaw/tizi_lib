define(function(require, exports) {
    require('tiziDialog');
    require('tizi_ajax');
    exports.loginForm = function(html)
    {
        $.tiziDialog({
            id:'loginFormID',
        	title:'用户登录',
            content:html,
            icon:null,
            width:400,
            ok:false
        });
        seajs.use("module/common/basics/common/login",function(ex){
        	ex.commonLogin();
        });
    }

    exports.loginCheckClick = function()
    {
        $('.loginCheck').live('click',function(){
            var redirect = $(this).attr('dest');
            exports.loginCheck(redirect);
        });
    }

    exports.loginCheck = function(redirect)
    {
        $.tizi_ajax({
            url: loginUrlName + 'login/check',
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
                    exports.loginForm(data.html);
                }
            }  
        });
    }
});