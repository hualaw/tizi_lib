define(function(require, exports) {
    exports.loginForm = function(html)
    {
        require('tiziDialog');
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
});