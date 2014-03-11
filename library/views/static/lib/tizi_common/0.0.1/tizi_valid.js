define(function(require, exports) {
    // 请求tizi_ajax
    require("tizi_ajax");
    // 请求验证库
    require("validForm");
    // 请求公共验证信息
    var sDataType = require("tizi_datatype").dataType();

    exports.indexLogin = function(callback_login){
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
                // 异步提交
                callback_login(data);
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
    // 首页--老师注册验证
    exports.indexRegisterTeacher = function() {
        $("input.subjectInput").val("0");
        var _Form = $(".regTeacherForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: 3,
            showAllError: false,
            ajaxPost: true,
            beforeSubmit: function(curform) {
                //选择学科表单验证
                if($("input.subjectInput").val()=="0"){
                    $(".subjectDiv").addClass("wrongBg");     
                    $(".subjectDiv span.Validform_checktip").remove();  
                    $(".subjectDiv").append($('<span class="Validform_checktip Validform_wrong">请选择学科</span>'));
                    return false;
                }else{
                    $(".subjectDiv").removeClass("wrongBg");     
                    $(".subjectDiv span.Validform_checktip").remove();  
                    $(".subjectDiv").append($('<span class="Validform_checktip Validform_right">&nbsp;</span>'));
                }
                /*调用验证码验证服务端信息*/
                if(require("tizi_validform").checkCaptcha('TeacherBox',1)){
                    // 加载MD5加密
                    require("tizi_validform").md5(curform);
                }else{
                    return false;
                }
            },
            callback: function(data) {
                // 异步提交
                require("tizi_validform").reset_md5('.regTeacherForm');
                if(!data.errorcode){
                    require.async("tizi_validform",function(ex){
                        // 提交注册结果
                        ex.changeCaptcha('TeacherBox');
                    });
                }
                require("module/common/ajax/RegisterResult").registerSubmit(data);
            }
        });
        _Form.addRule([ {
            ele: ".teacherName",
            datatype: sDataType.Email.datatype,
            nullmsg: sDataType.Email.nullmsg,
            errormsg: sDataType.Email.errormsg
        }, 
        {
            ele: ".teacherPassword",
            datatype: sDataType.Passwd.datatype,
            nullmsg: sDataType.Passwd.nullmsg,
            errormsg: sDataType.Passwd.errormsg
        }, 
        {
            ele: ".teacherRePassword",
            recheck:'t_password',
            datatype: sDataType.Passwd.datatype,
            nullmsg: sDataType.Passwd.re_nullmsg,
            errormsg: sDataType.Passwd.re_errormsg
        }, 
        {
            ele: ".teacherNames",
            datatype: sDataType.URname.datatype,
            nullmsg: sDataType.URname.nullmsg,
            errormsg: sDataType.URname.errormsg
        }, 
        // {
        //     ele: ".subjectInput",
        //     datatype: "zh",
        //     nullmsg: "请选择学科",
        //     errormsg: "未选择学科"
        // },
        {
            ele:".TeacherBoxWord",
            datatype:sDataType.CaptchaCode.datatype,
            nullmsg:sDataType.CaptchaCode.nullmsg,
            errormsg:sDataType.CaptchaCode.errormsg
        },{
            ele: ":checkbox:first",
            datatype: "*",
            nullmsg: "请先同意协议",
            errormsg: "您未同意协议！"
        } ]);
    };
    // 老师注册成功绑定我的学科
    exports.bindMySubject = function(){
        var _Form=$(".myPerfectSubjectForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            beforeSubmit:function(curform){
                if($('.myPerfectSubjectForm .active').length >0){
                    $('.bindMySubject').val($('.myPerfectSubjectForm .active').attr('sid'));
                }else{
                    // 请求dialog插件
                    require.async("tiziDialog",function(){
                        $.tiziDialog({
                            content:"请选择一个您的默认学科，在使用组卷功能时将默认看到该学科内容，默认学科可以进入个人中心修改。",
                            icon:null
                        });
                    });
                    return false;
                }
            },
            ajaxPost:true,
            callback:function(data){
                // 异步提交
                require.async("module/common/ajax/RegisterResult",function(ex){
                    // 提交注册结果
                    ex.myperfect(data);
                });               
                // CommonAjax.Register.mysubject(data);
            }
        });
    }
    //学生注册成功绑定我的用户名
    exports.bindMyUname=function(){
        var _Form=$(".myPerfectUnameForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                 require.async("module/common/ajax/RegisterResult",function(ex){
                    // 提交注册结果
                    ex.myperfect(data);
                });
            }
        });
        _Form.addRule(
            [
                {
                    ele:".bindMyUname",
                    datatype:sDataType.Uname.datatype,
                    nullmsg:sDataType.Uname.nullmsg,
                    errormsg:sDataType.Uname.errormsg
                }
            ]
        );
    };
    // 学生注册成功绑定我的班级
    exports.bindMyGrade = function(){
        var _Form=$(".myPerfectGradeForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            beforeSubmit:function(curform){
                if($('.myPerfectGradeForm .active').length >0){
                    $('.bindMyGrade').val($('.myPerfectGradeForm .active').attr('gid'));
                }else{
                    // 请求dialog插件
                    require.async("tiziDialog",function(){
                        $.tiziDialog({
                            content:"请选择你的年级，系统会推送与您年级相关的学习内容，你也可以进入个人中心修改年级。",
                            icon:null
                        });
                    });
                    return false;
                }
            },
            ajaxPost:true,
            callback:function(data){
                // 异步提交
                require.async("module/common/ajax/RegisterResult",function(ex){
                    // 提交注册结果
                    ex.myperfect(data);
                });
                // CommonAjax.Register.mygrade(data);
            }
        });
    };
    // 首页--学生注册验证
    exports.indexRegisterStudent = function() {
        $("input.gradeInput").val("0");
        var _Form = $(".regStudentForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: 3,
            showAllError: false,
            ajaxPost: true,
            beforeSubmit: function(curform) {
                //选择年级 表单验证
                $(".gradeDiv").removeClass("wrongBg"); 
                if($("input.gradeInput").val()=="0"){
                    $(".gradeDiv").addClass("wrongBg");    
                    $(".gradeDiv span.Validform_checktip").remove();  
                    $(".gradeDiv").append($('<span class="Validform_checktip Validform_wrong">请选择年级</span>'));
                    return false;
                }else{  
                    $(".gradeDiv").removeClass("wrongBg");     
                    $(".gradeDiv span.Validform_checktip").remove();               
                    $(".gradeDiv").append($('<span class="Validform_checktip Validform_right">&nbsp;</span>'));
                }
                /*调用验证码验证服务端信息*/
                if(require("tizi_validform").checkCaptcha('StudentBox',1)){
                    // 加载MD5加密
                    require("tizi_validform").md5(curform);
                }else{
                    return false;
                }
            },
            callback: function(data) {
                // 异步提交
                require("tizi_validform").reset_md5('.regStudentForm');
                if(!data.errorcode){
                    require.async("tizi_validform",function(ex){
                        // 提交注册结果
                        ex.changeCaptcha('StudentBox');
                    });
                }
                require("module/common/ajax/RegisterResult").registerSubmit(data);
            }
        });
        _Form.addRule([ {
            ele: ".studentName",
            datatype: sDataType.Uname.datatype,
            nullmsg: sDataType.Uname.nullmsg,
            errormsg: sDataType.Uname.errormsg
        }, {
            ele: ".studentPassword",
            datatype: sDataType.Passwd.datatype,
            nullmsg: sDataType.Passwd.nullmsg,
            errormsg: sDataType.Passwd.errormsg
        }, {
            ele: ".studentrePassword",
            recheck:'s_password',
            datatype: sDataType.Passwd.datatype,
            nullmsg: sDataType.Passwd.re_nullmsg,
            errormsg: sDataType.Passwd.re_errormsg
        },{
            ele: ".studentNames",
            datatype: sDataType.URname.datatype,
            nullmsg: sDataType.URname.nullmsg,
            errormsg: sDataType.URname.errormsg
        },{
            ele:".StudentBoxWord",
            datatype:sDataType.CaptchaCode.datatype,
            nullmsg:sDataType.CaptchaCode.nullmsg,
            errormsg:sDataType.CaptchaCode.errormsg
        },{
            ele: ":checkbox:first",
            datatype: "*",
            nullmsg: "请先同意协议",
            errormsg: "您未同意协议！"
        } ]);
    };
    // 首页--家长注册验证
    exports.indexRegisterParents = function() {
        var _Form = $(".regParentForm").Validform({
            // 自定义tips在输入框上面显示
            tiptype: 3,
            showAllError: false,
            ajaxPost: true,
            beforeSubmit: function(curform) {
                /*调用验证码验证服务端信息*/
                if(require("tizi_validform").checkCaptcha('ParentBox',1)){
                    // 加载MD5加密
                    require("tizi_validform").md5(curform);
                }else{
                    return false;
                }
            },
            callback: function(data) {
                // 异步提交
                require("tizi_validform").reset_md5('.regParentForm');
                if(!data.errorcode){
                    require.async("tizi_validform",function(ex){
                        // 提交注册结果
                        ex.changeCaptcha('ParentBox');
                    });
                }
                require("module/common/ajax/RegisterResult").registerSubmit(data);
            }
        });
        _Form.addRule([ {
            ele: ".parentName",
            datatype: sDataType.Email.datatype,
            nullmsg: sDataType.Email.nullmsg,
            errormsg: sDataType.Email.errormsg
        }, {
            ele: ".parentPassword",
            datatype: sDataType.Passwd.datatype,
            nullmsg: sDataType.Passwd.nullmsg,
            errormsg: sDataType.Passwd.errormsg
        }, {
            ele: ".parentrePassword",
            recheck:'p_password',
            datatype: sDataType.Passwd.datatype,
            nullmsg: sDataType.Passwd.re_nullmsg,
            errormsg: sDataType.Passwd.re_errormsg
        },{
            ele: ".parentNames",
            datatype: sDataType.URname.datatype,
            nullmsg: sDataType.URname.nullmsg,
            errormsg: sDataType.URname.errormsg
        },
        // {
        //     ele: ".gradeInput",
        //     datatype: "zh",
        //     nullmsg: "请选择年级",
        //     errormsg: "未选择年级"
        // },
        {
            ele:".ParentBoxWord",
            datatype:sDataType.CaptchaCode.datatype,
            nullmsg:sDataType.CaptchaCode.nullmsg,
            errormsg:sDataType.CaptchaCode.errormsg
        },{
            ele: ":checkbox:first",
            datatype: "*",
            nullmsg: "请先同意协议",
            errormsg: "您未同意协议！"
        } ]);
    };
    exports.resetIndexRegisterForms = function() {
        $(".indexLoginForm").Validform().resetForm();
        $(".regTeacherForm").Validform().resetForm();
        $(".regStudentForm").Validform().resetForm();
        $(".regParentForm").Validform().resetForm();
        $(".ValidformInfo").hide();
    };
    // 忘记密码--通过用户名找回
    exports.forgetPwName = function(){
        var _Form=$(".forgetPwNameForm").Validform({
                // 3说明是在输入框右侧显示
                tiptype:3,
                showAllError:false,
                ajaxPost:true,
                beforeSubmit:function(){        
                    /*调用验证码验证服务端信息*/
                    return require("tizi_validform").checkCaptcha('ForgotBox',1);
                },
                callback:function(data){
                    // require("module/common/ajax/findPasswordResult").reset_mode(data);
                    if(!data.errorcode){
                        require.async("tizi_validform",function(ex){
                            // 提交注册结果
                            ex.changeCaptcha('ForgotBox');
                        });
                    }
                    // 异步提交
                    require.async("module/common/ajax/findPasswordResult",function(ex){
                        // 提交注册结果
                        ex.reset_mode(data);
                    });
                }
            });
            _Form.addRule(
                [
                    {
                        ele:".forgetPwName",
                        datatype:sDataType.Username.datatype,
                        nullmsg:sDataType.Username.nullmsg,
                        errormsg:sDataType.Username.errormsg
                    },{
                        ele:".ForgotBoxWord",
                        datatype:sDataType.CaptchaCode.datatype,
                        nullmsg:sDataType.CaptchaCode.nullmsg,
                        errormsg:sDataType.CaptchaCode.errormsg

                    }
                ]
            );
    };
    // 忘记密码--通过手机号找回
    exports.forgetPwTel = function(){
        var _Form=$(".forgetPwTelForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            beforeSubmit:function(){  
                /*调用验证码验证服务端信息*/
                var checkcode = $('.forgetTelCaptap').val();
                var phone = $('.forgetTelNum').val();
                return require("tizi_validform").checkPhoneCode(checkcode,phone,2);
            },
            callback:function(data){
                // 异步提交
                require.async("module/common/ajax/findPasswordResult",function(ex){
                    // 提交注册结果
                    ex.reset(data);
                });
                // CommonAjax.findPassword.reset(data);
            }
        });
        _Form.addRule(
            [
                {
                    ele:".forgetTelCaptap",
                    datatype:sDataType.PhoneCode.datatype,
                    nullmsg:sDataType.PhoneCode.nullmsg,
                    errormsg:sDataType.PhoneCode.errormsg

                },{
                    ele:".ForgotBoxWord",
                    datatype:sDataType.PhoneCode.datatype,
                    nullmsg:sDataType.PhoneCode.nullmsg,
                    errormsg:sDataType.PhoneCode.errormsg

                }
            ]
        );
    };
    // 重置密码验证
    exports.restPassword = function(){
        var _Form=$(".restPasswordForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            beforeSubmit:function(curform){
                // 加载MD5加密
                require("tizi_validform").md5(curform);
            },
            ajaxPost:true,
            callback:function(data){
                require('tizi_validform').reset_md5('.restPasswordForm');
                // 异步提交
                require.async("module/common/ajax/findPasswordResult",function(ex){
                    // 提交注册结果
                    ex.submit(data);
                });
            }
        });
        _Form.addRule(
            [
                {
                    ele:".setPassword",
                    datatype:sDataType.Passwd.datatype,
                    nullmsg:sDataType.Passwd.new_nullmsg,
                    errormsg:sDataType.Passwd.errormsg
                },
                {
                    ele:".reSetPassword",
                    datatype:sDataType.Passwd.datatype,
                    recheck:"password",
                    nullmsg:sDataType.Passwd.re_new_nullmsg,
                    errormsg:sDataType.Passwd.re_errormsg
                }
            ]
        );
    };
    // 直接输入电话号码找回
    exports.findPwOnlyTel = function(){
        var _Form=$(".findPwOnlyTelForm").Validform({
            // 3说明是在输入框右侧显示
            tiptype:3,
            showAllError:false,
            ajaxPost:true,
            callback:function(data){
                // 异步提交
                require.async("module/common/ajax/findPasswordResult",function(ex){
                    // 提交注册结果
                    ex.apply(data);
                });
                // CommonAjax.findPassword.apply(data);
            }
        });
        _Form.addRule(
            [
                {
                    ele:".findPwOnlyTelName",
                    datatype:sDataType.Phone.datatype,
                    nullmsg:sDataType.Phone.nullmsg,
                    errormsg:sDataType.Phone.errormsg
                }
            ]
        );
    };
    // 全站头部用户反馈验证
    exports.FeedbackCheck = function(){
        var varFeedbackCheck = $(".feedbackForm").Validform({
            tiptype:3,
            showAllError:true,
            ajaxPost:true,
            beforeSubmit:function(){            
                /*调用验证码验证服务端信息*/
                var checkcode = $('.imgCaptcha').val();
                return require('tizi_validform').changeCaptcha(checkcode);
                // return Common.comValidform.checkCaptcha(checkcode);
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