define(function(require, exports) {
    require('tiziDialog');
    require('tizi_ajax');
    // 请求验证库
    require("validForm");
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();

    exports.authForm = function(html){
        $.tiziDialog({
            id:'authFormID',
        	title:'手机验证',
            content:html,
            icon:null,
            width:530,
            ok:function(){
                $('.modifyMyPhoneFormSubmit').submit();
                return false;
            },
            close:function(){
                
            }
        });
        // 发送手机验证码
        seajs.use('tizi_msgsend',function(ex){
          ex.sms.init($('.modifyPhone').val(),4,'.modifyPhone');
        });
        // 验证规则
        var _Form = $(".modifyMyPhoneForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: 3,
            showAllError: false,
            beforeSubmit: function(curform) { 
                /*调用验证码验证服务端信息*/
                if(!require("tizi_validform").checkPhoneCode($('.forgetTelCaptap').val(),$('.modifyPhone').val(),4)){
                    return false;
                }
            },
            ajaxPost: true,
            callback: function(data) {
                if(data.errorcode){
                    $.tiziDialog.list['authFormID'].close();
                    $.tiziDialog({icon:"succeed",content:data.error});
                }else{
                    $.tiziDialog({icon:"error",content:data.error});
                }
            }
        });
        _Form.addRule([{
                ele: ".modifyPhone",
                datatype: sDataType.Phone.datatype,
                nullmsg: sDataType.Phone.nullmsg,
                errormsg: sDataType.Phone.errormsg
            },{
                ele: ".forgetTelCaptap",
                datatype: sDataType.PhoneCode.datatype,
                nullmsg: sDataType.PhoneCode.nullmsg,
                errormsg: sDataType.PhoneCode.errormsg
            }
            
        ]);
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