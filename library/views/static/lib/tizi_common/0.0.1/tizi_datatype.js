define(function(require, exports) {
    exports.dataType = function() {
        var _type = {
            //用户真实姓名
            URname:{
                datatype:"*2-20",
                nullmsg:"请输入真实姓名",
                errormsg:"长度2-20个字符"
            },
            //用户名
            Uname:{
                datatype:"/^[a-zA-Z]{1}\\w{5,17}$/",
                nullmsg:"请输入用户名",
                errormsg:"以英文字母开头，6-18个英文或数字"
            },
            //学号
            Stuid:{
                datatype:"/^\\d{8,10}$/",
                nullmsg:"请输入学号",
                errormsg:"长度8-10个数字"
            },
            //登录用户名
            Username:{
                datatype:"e | *6-20",
                nullmsg:"请输入邮箱/手机号/用户名/学号",
                nullmsg_nostuid:"请输入邮箱/手机号/用户名",
                errormsg:"长度6-20个字符"
            },
            //email
            Email:{
                datatype:"e",
                nullmsg:"请输入邮箱地址",
                errormsg:"请输入正确的邮箱地址"
            },
            //phone
            Phone:{
                datatype:"m",
                nullmsg:"请输入手机号码",
                errormsg:"请输入正确的手机号码"
            },
            //phone code，6位数字
            PhoneCode:{
                datatype:"/^\\d{6}$/",
                datatype_allownull:"/^\\d{6}$/ | /^\\w{0}$/",
                nullmsg:"请输入验证码",
                errormsg:"验证码长度是6位"
            },
            //captcha code，4位数字字母
            CaptchaCode:{
                datatype:"/^\\w{4}$/",
                nullmsg:"请输入验证码",
                errormsg:"验证码长度是4位"
            },
            //qq
            QQ:{
                datatype:"n5-12 | /^\\w{0}$/",
                datatype_nonull:"n5-12",
                nullmsg:"请输入QQ号码！",
                errormsg:"长度5-12个数字"
            },
            //用户密码Passwd
            Passwd:{
                datatype:"*6-16",
                nullmsg:"请输入密码",
                errormsg:"长度6-16个字符之间",
                old_nullmsg:"请输入旧密码",
                re_nullmsg:"请再次输入密码",
                re_errormsg:"两次密码不一致！",
                new_nullmsg:"请输入新密码",
                re_new_nullmsg:"请再次输入新密码"
            },
            //个性域名
            Dname:{
                datatype:"/^[a-zA-Z]{1}[a-zA-Z_0-9]{3,17}$/",
                nullmsg:"请输入个性域名",
                errormsg:"以英文字母开头，4-18个英文，数字或下划线"
            },
            //单位名称
            Oname:{
                datatype:"*2-20",
                nullmsg:"请输入单位名称",
                errormsg:"长度2-20个字符"
            },
            Classname:{
                datatype:"*1-12",
                nullmsg:"请输入班级名称",
                errormsg:"长度1-12个字符"
            },
            Classnum:{
                datatype:"n6-8",
                nullmsg:"请输入班级编号",
                errormsg:"长度6-8个数字"
            },
            radioValid:{
                datatype:"*"
            }
        };
        return _type;
    };
});