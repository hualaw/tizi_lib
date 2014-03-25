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

    exports.loginCheck = function()
    {
        $('.loginCheck').live('click',function(){
            var redirect = $(this).attr('dest');
            $.tizi_ajax({
                url: baseUrlName + 'login/check',
                type: "POST",
                dataType: "json",
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
        });
    }
});