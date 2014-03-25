define(function(require, exports) {
    $.tiziDialog({
    	title:'用户登录',
        content:$('#comLogin').html().replace('indexLoginForm_beta','indexLoginForm'),
        icon:null,
        width:400,
        ok:false
    });
    seajs.use("module/common/basics/common/login",function(ex){
    	ex.commonLogin();
    });
});