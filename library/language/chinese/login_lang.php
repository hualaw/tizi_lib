<?php
$lang['default_error']=$lang['error_default']="系统繁忙，请稍后再试";
$lang['default_error_login']="页面已失效，请您首先登录";
$lang['default_error_token']="页面已失效，请您刷新页面";//"对话框超时";

//user type
$lang['error_user_type_teacher']="您的身份不是老师";
$lang['error_user_type_student']="您的身份不是学生";
$lang['error_user_type_parent']="您的身份不是家长";

/*zujuan token errormsg*/
$lang['error_invalid_token']="页面已失效，请刷新页面";//"对话框超时";
$lang['error_check_token']=$lang['error_invalid_token'];//"验证超时，请刷新页面";
$lang['error_generate_token']=$lang['error_invalid_token'];//"验证超时，请刷新页面";
$lang['error_reg_invalid_token']=$lang['error_invalid_token'];//"注册超时";
$lang['error_login_invalid_token']=$lang['error_invalid_token'];//"登录超时";
$lang['error_invalid_user']="帐户已被禁用，请联系管理员";
$lang['error_login_invalid_thrift']=$lang['default_error'];

/*zujuan register errormsg*/
$lang['error_reg_confirm_password']="两次输入的密码不一致";
$lang['error_reg_insert']="注册失败";
$lang['error_reg_user_type']="未知的用户类型";
$lang['error_reg_exist_email']="邮箱已存在，无法注册";
$lang['error_reg_exist_phone']="手机已存在，无法注册";
$lang['error_reg_exist_uname']="用户名已存在，无法注册";
$lang['error_reg_re_login']="您已登录，无法注册";

/*zujuan sms errormsg*/
$lang['error_sms_invalid_phone']="无效的手机号码";
$lang['error_sms_normal']="短信服务不可用";
$lang['error_sms_code']='验证码错误或已失效';
$lang['error_sms_exist_phone']="手机已存在";
$lang['error_sms_invalid_phone']="手机不存在";

/*zujuan captcha errormsg*/
$lang['error_captcha_code']='验证码错误或已失效';

/*zujuan login errormsg*/
//$lang['error_login_not_verify']="用户未激活";
//$lang['error_login_not_verify_email']="登录邮箱未验证";
//$lang['error_login_not_verify_phone']="登录手机未验证";
$lang['error_login_error_username_or_password']="用户名或密码错误";
$lang['error_login_invalid_username']=$lang['error_login_error_username_or_password'];
$lang['error_login_invalid_type']=$lang['error_login_error_username_or_password'];
$lang['error_login_not_verify']=$lang['error_login_error_username_or_password'];
$lang['error_login_not_verify_email']=$lang['error_login_error_username_or_password'];
$lang['error_login_not_verify_phone']=$lang['error_login_error_username_or_password'];
$lang['error_login_first']="请您首先登录";
$lang['error_login_is_block']=$lang['error_invalid_user'];
$lang['login_please'] = $lang['error_login_first'];//'您的登录已超时，是否重新登陆？';
$lang['sh_login_error'] = $lang['error_login_first'];//'未登陆';
$lang['error_login']= $lang['error_login_first'];//"请您登陆";

/*zujuan send auth email*/
$lang['error_send_auth_email']=$lang['default_error'];//"邮件服务不可用，请您稍后再试";
$lang['success_send_auth_email']="邮件已发送到您的邮箱";
$lang['error_send_auth_phone']=$lang['default_error'];//"短信服务不可用，请您稍后再试";
$lang['success_send_auth_phone']="短信已发送";
$lang['error_send_authcode_email']="请不要频繁发送验证邮件";
$lang['error_send_authcode_phone']="请不要频繁发送验证短信";

/*zujuan errormsg*/
$lang['error_change_default']=$lang['default_error'];//"修改失败";
$lang['success_change_default']="修改成功";
$lang['error_authcode_email_limit']="请不要频繁发送验证邮件";
$lang['error_authcode_phone_limit']="请不要频繁发送验证短信";
$lang['error_verify_email_failed']="无效的验证邮件";
$lang['error_password']="登录密码输入错误";
$lang['error_auth_code']="验证码错误或已失效";
$lang['error_exist_email']="邮箱已存在，无法绑定";
$lang['error_exist_phone']="手机已存在，无法绑定";
$lang['error_exist_uname']="用户名已存在，无法绑定";
$lang['error_invalid_name']="姓名不能为空";
$lang['error_invalid_email']="无效的邮箱地址";
$lang['error_invalid_phone']="无效的手机号码";
$lang['error_invalid_qq']="无效的QQ号码";
$lang['error_invalid_mysubject']="无效的注册学科";
$lang['error_invalid_mygrade']="无效的注册年级";
$lang['error_invalid_password']="无效的登录密码";
$lang['error_invalid_uname']="无效的用户名";
$lang['error_invalid_student_id']="无效的学生号码";
$lang['error_invalid_confirm_password']=$lang['error_reg_confirm_password'];

/*zujuan reset password errormsg*/
$lang['error_reset_password_not_user']="对不起，没有找到您的用户信息";
$lang['error_reset_password_not_verify_email']="对不起，没有找到您的邮箱地址";
$lang['error_reset_password_not_verify_phone']="对不起，没有找到您的手机号码";
$lang['error_reset_password_failed']=$lang['default_error'];//"重置密码失败";
$lang['error_reset_password_auth_code_failed']="验证码错误或已失效";
$lang['error_reset_password']="密码不能为空";

/*download*/
$lang['download_student_id_head']="您好！\r\n感谢您注册梯子网！\r\n请您牢记您的学号：";
$lang['download_student_id_end']="\r\n和登录密码，此号码将用作登录的用户名，\r\n为了便于您顺利找回登录密码，请尽快绑定邮箱或手机。\r\n\r\n梯子网www.tizi.com";

/*feedback*/
$lang['error_feedback_content']="请您输入有效的反馈信息";
$lang['error_feedback_qq']="请您输入正确的QQ号码";
$lang['success_feedback']="反馈信息提交成功！<br />衷心感谢您的宝贵意见，我们将会尽快处理。";