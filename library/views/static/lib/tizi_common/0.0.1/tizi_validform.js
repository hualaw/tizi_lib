define(function(require, exports) {
    require('tizi_ajax');
    // md5
	exports.md5 = function(element) {
        require("md5");
        var i = 0;
        element.find('input').each(function(index){
            if($(this).attr('type')=='password'){
                var input_name = $(this).attr('name');
                if(input_name==undefined) input_name = $(this).attr('md5_name');
                if(input_name) i = i + 1;
                var input_id = input_name + i;
                var pass_hidden = '<input class="text-input" id="' + input_id + '_md5" name="' + input_name + '" type="hidden">';
                $('#' + input_id + '_md5').remove();
                $(this).parent().append(pass_hidden);
                $('#' + input_id + '_md5').val(tizi_md5($(this).val()));
                $(this).attr('md5_name',input_name).removeAttr('name');
            }
        });
    };
    //重置md5
    exports.reset_md5 = function(element){
        var i = 0;
        $(element).find('input').each(function(index){
            if($(this).attr('type')=='password'){
                input_name = $(this).attr('md5_name');
                if(input_name) i = i + 1;
                var input_id = input_name + i;
                $('#' + input_id + '_md5').remove();
                $(this).attr('name',input_name).removeAttr('md5_name');
            }
        });
    };
    // 加载验证码
    exports.changeCaptcha = function(captcha_name){
        //if(captcha_name == undefined) captcha_name = basePageName;
        if(captcha_name == undefined) return false;
        var img = $('.'+captcha_name).siblings("img");
        var now = (new Date).valueOf();
        //var url =  baseUrlName + "captcha?captcha_name="+captcha_name+"&ver=" + now;
        $.tizi_ajax({
            url:baseUrlName + "captcha",
            type:'get',
            dataType:"json",
            data:{'captcha_name':captcha_name,ver:(new Date).valueOf()},
            success:function (data) {
                if(data.errorcode){
                    img.attr('src',data.image);
                    if(data.word) {
                        $('.'+captcha_name).parent().addClass('undis');
                        $('.'+captcha_name+'Word').val(data.word);
                    }else{
                        $('.'+captcha_name).parent().removeClass('undis');
                    }
                }else{
                    require.async('tiziDialog',function(){
                        $.tiziDialog({
                            icon:'error',
                            content:data.error,
                            time:3
                        })
                    });
                }
            }
        });
    };
    //更换验证码
    exports.bindChangeVerify = function(captcha_name){
        if($('.commonCaptcha').find('.'+captcha_name)){
            $('.'+captcha_name).siblings('.changeCaptcha').each(function(){
                $(this).live('click',function(event){
                    event.preventDefault();
                    exports.changeCaptcha(captcha_name);
                });
            });
        }
    };
    // 提交数据检测验证码
    exports.checkCaptcha = function(captcha_name,keep_code,show_dialog,checkcode){
        var check = false;
        if(captcha_name == undefined) captcha_name = basePageName;
        if(checkcode == undefined) checkcode = $('.'+captcha_name+'Word').val();
        if(keep_code == undefined) keep_code = 1;
        $.tizi_ajax({
            url: baseUrlName + "check_captcha",
            type: 'get',
            dataType: "json",
            async:false,
            data:{'check_code':checkcode,'keep_code':keep_code,'captcha_name':captcha_name,ver:(new Date).valueOf()},
            success:function(data){
                if(data.errorcode){
                    $('.textCaptcha').siblings('.Validform_checktip').text(data.error).attr('class','Validform_checktip Validform_right');
                    check = true;
                }else{
                    if(show_dialog) {
                        require.async('tiziDialog',function(){
                            $.tiziDialog({
                                icon:'error',
                                content:data.error,
                                time:3
                            })
                        });
                    };
                    $(".commonCaptcha .Validform_checktip").text(data.error).attr('class','Validform_checktip Validform_wrong');
                    //require.async("tizi_validform",function(ex){
                        // 提交注册结果
                        //ex.changeCaptcha(captcha_name);
                    //});
                    //$('.textCaptcha').siblings('.Validform_checktip').text(data.error).attr('class','Validform_checktip Validform_wrong');
                    check = false;
                }
            }
        });
        return check;
    };
    // 发送手机验证码
    exports.sendPhoneCode = function(phone,code_type,fn,errfn){
        var url =  baseUrlName + "send_phone_code";
        require('tiziDialog');
        $.tizi_ajax({
            url:url,
            type:'post',
            dataType:"json",
            data:{phone:phone,code_type:code_type,ver:(new Date).valueOf()},
            success:function (data) {
                if (data.errorcode) {
                    fn(data);
                }else{
                    errfn(data);
                }
            }
        });
    };
    //发送邮箱验证码
    exports.sendEmailCode  = function(email,code_type,fn,errfn){
        var url =  baseUrlName + "send_email_code";
        $.tizi_ajax({
            url:url,
            type:'post',
            dataType:"json",
            data:{email:email,code_type:code_type,ver:(new Date).valueOf()},
            success:function (data) {
                if (data.errorcode) {
                    fn();
                }else{
                    errfn(data);
                }
            }
        });
    };
    //检测手机验证码
    exports.checkPhoneCode = function(checkcode,phone,code_type){
        var url =  baseUrlName + "check_code";
        var check = false;
        require('tizi_ajax');
        $.tizi_ajax({
            url:url,
            type:'post',
            dataType: "json",
            async:false,
            data:{'phone':phone,'check_code':checkcode,'code_type':code_type,ver:(new Date).valueOf()},
            success:function(data){ 
                if(data.errorcode){
                    $('.phoneCode').siblings('.Validform_checktip').text(data.error).attr('class','Validform_checktip Validform_right');
                    check = true;
                }
                else{
                    $('.phoneCode').siblings('.Validform_checktip').text(data.error).attr('class','Validform_checktip Validform_wrong');
                    check = false;
                }
            }
        });
        return check;
    };

    //flash环境检测
    exports.detectFlashSupport=function(fn_noflash,fn_flash){
        if(fn_noflash == undefined) fn_noflash = function(){}
        if(fn_flash == undefined) fn_flash = function(){}
        var hasFlash = false;
        if (typeof ActiveXObject === "function") {
          try {
            if (new ActiveXObject("ShockwaveFlash.ShockwaveFlash")) {
              hasFlash = true;
            }
          } catch (error) {}
        }
        if (!hasFlash && navigator.mimeTypes["application/x-shockwave-flash"]) {
          hasFlash = true;
        }
        if(!hasFlash){
            fn_noflash();
        }else{
            fn_flash();
        }
    };

    //登录检测
    exports.checkLogin=function(){
        require('cookies');
        var username = $.cookies.get(baseUnID);
        if(username){
            $.tizi_ajax({
                url:baseUrlName+'login/login/check_login',
                type:"get",
                dataType:"json",
                data:{},
                success:function(data){
                    if(data.errorcode) window.location.href = baseUrlName;
                }
            });
        }
    };
});