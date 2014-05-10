define(function(require, exports) {
    // 请求tizi_ajax
    require("tizi_ajax");
    require('tiziDialog');
    // 请求验证库
    require("validForm");
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();

    exports.indexLogin = function(callback_login){
         // 加载placeHolder插件
        seajs.use('placeHolder',function(ex){
            ex.JPlaceHolder.init();
        });
        var _Form = $(".indexLoginForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: function(msg, o, cssctl) {
                if (!o.obj.is("form")) {
                    var objtip = o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    o.obj.next().show();
                    var objtip = o.obj.next().find(".Validform_checktip");
                    objtip.text(msg);
                    var infoObj = o.obj.next(".ValidformTips");
                    // 判断验证成功
                    if (o.type == 2) {
                        infoObj.show();
                        o.obj.next().hide();
                    }
                }
            },
            showAllError: false,
            beforeSubmit: function(curform) {
                // 加载MD5加密
                require.async("tizi_validform",function(ex){
                    ex.md5(curform);
                });
            },
            ajaxPost: true,
            callback: function(data) {
                require("tizi_validform").reset_md5('.indexLoginForm');
                if(callback_login == undefined){
                    if(data.errorcode){
                        if($.tiziDialog.list['loginFormID']) $.tiziDialog.list['loginFormID'].close();
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
                            seajs.use('module/common/ajax/loginForm/' + callback, function(ex){
                                ex.callback();
                            });
                        }else if(data.redirect){
                            window.location.href=data.redirect;
                        }
                    }else{
                        // 请求dialog插件
                        require.async("tiziDialog",function(){
                            $.tiziDialog({content:data.error});
                        });
                    }
                }else{
                    callback_login(data);
                }

            }
        });
        _Form.addRule([{
                ele: ".username",
                datatype: sDataType.Username.datatype,
                nullmsg: sDataType.Username.nullmsg,
                errormsg: sDataType.Username.errormsg
            },
            {
                ele: ".password",
                datatype: sDataType.Passwd.datatype,
                nullmsg: sDataType.Passwd.nullmsg,
                errormsg: sDataType.Passwd.errormsg
            }
        ]);
    };
    // 全站头部用户反馈验证
    exports.FeedbackCheck = function(){
        var varFeedbackCheck = $(".feedbackForm").Validform({
            tiptype:3,
            showAllError:true,
            ajaxPost:true,
            beforeSubmit:function(){            
                /*调用验证码验证服务端信息*/
                return require('tizi_validform').checkCaptcha('feedbackBox',1);
            },
            callback:function(data) {
                // 异步提交
                require.async("tizi_commonajax",function(ex){
                    // 提交注册结果
                    ex.feedbacksubmit(data);
                });      
            }
        });
        varFeedbackCheck.addRule([
            {
                // 检测反馈内容
                ele:".contentTextarea",
                datatype:"*5-1000",
                nullmsg:"请填写反馈内容！",
                errormsg:"反馈内容5-1000个字符之间！"
            },
            {
                // 检测QQ
                ele:".QQText",
                datatype:sDataType.QQ.datatype,
                errormsg:sDataType.QQ.errormsg
            },
            {
                // 检测QQ未登录
                ele:".QQTextUnlogin",
                datatype:sDataType.QQ.datatype_nonull,
                nullmsg:sDataType.QQ.nullmsg,
                errormsg:sDataType.QQ.errormsg
            },
            {
                // 检测验证码
                ele:".textCaptcha",
                datatype:sDataType.CaptchaCode.datatype,
                nullmsg:sDataType.CaptchaCode.nullmsg,
                errormsg:sDataType.CaptchaCode.errormsg
            }
        ]);
    };
});