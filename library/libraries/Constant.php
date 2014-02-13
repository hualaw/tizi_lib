<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

Class CI_Constant {

	/*zujuan user type*/
	const USER_TYPE_ADMIN = 1;
	const USER_TYPE_STUDENT = 2;
	const USER_TYPE_TEACHER = 3;
	const USER_TYPE_PARENT = 4;

	/*zujuan register origien*/
	const REG_ORIGEN_WEB_PHONE = 1;
	const REG_ORIGEN_WEB_EMAIL = 2;
	const REG_ORIGEN_WEB_STUID = 3;
	const REG_ORIGEN_WEB_UNAME = 4;
	const REG_ORIGEN_AQ_IOS = 21;
	const REG_ORIGIN_AQ_ANDROID = 31;
	const REG_ORIGIN_CRM = 41;
	const REG_ORIGIN_CRM_STUID= 43;

	/*zujuan session and cookie expire*/
	const SESSION_EXPIRE_TIME = "2 hour";
	const COOKIE_EXPIRE_TIME = 0;//14400-4hour,0-with session expire
	const COOKIE_INVITE_EXPIRE_TIME = 86400;//邀请码过期时间24小时
	const COOKIE_REMEMBER_EXPIRE_TIME = 604800;//七天免登陆
	const COOKIE_MYSUBJECT_EXPIRE_TIME = 0;//Favorate随浏览器
	const COOKIE_TIPS_EXPIRE_TIME = 604800;//tips保存七天
	const PAGE_TOKEN_LIFE_CIRCLE = 604800;/*Page Token 过期时间 单位(秒)*/
	const NO_PASSWORD_EXPIRE_TIME = 1800;//用户登录后，免输入密码验证的过期时间,30分钟

	const COOKIE_TZUSERNAME = "TZU";//自动登录cookie name
	const COOKIE_TZMYSUBJECT = "_ms";//mysubject cookie name
	const COOKIE_TZMYSUBJECT_PAPER = "_msp";//mysubject cookie name
	const COOKIE_TZMYSUBJECT_DOC = "_msd";//mysubject cookie name
	const COOKIE_TZMYSUBJECT_HOMEWORK = "_msh";//mysubject cookie name
	const COOKIE_TZTIPS = "_tp_";//tips cookie perfix name
	const COOKIE_NOREDIS = "_nrd";//no redis cookie name
	const COOKIE_INVITE = "invite";//invite cookie name

	/*zujuan login errorcode*/
	const LOGIN_SUCCESS = 1;
	const LOGIN_NOT_VERIFY = 2;
	const LOGIN_NOT_VERIFY_EMAIL = 3;
	const LOGIN_NOT_VERIFY_PHONE = 4;
	const LOGIN_INVALID_USERNAME = 5;
	const LOGIN_ERROR_USERNAME_OR_PASSWORD = 6;
	const LOGIN_INVALID_TYPE = 7;
	const LOGIN_IS_BLOCK = 8;
	const LOGIN_INVALID_THRIFT = 9;
	//login
	const LOGIN_NEED_EMAIL_VERIFY = false;

	/*zujuan login type*/
	const LOGIN_TYPE_EMAIL = 1;
	const LOGIN_TYPE_PHONE = 2;
	const LOGIN_TYPE_STUID = 3;
	const LOGIN_TYPE_UNAME = 4;
	const LOGIN_TYPE_ERROR = 9;

	/*zujuan verify type*/
	const VERIFY_TYPE_EMAIL = 1;
	const VERIFY_TYPE_PHONE = 2;

	/*zujuan verify code type*/
	const CODE_TYPE_REGISTER = 1;
	const CODE_TYPE_CHANGE_PASSWORD = 2;
	const CODE_TYPE_CHANGE_EMAIL = 3;
	const CODE_TYPE_CHANGE_PHONE = 4;
	const CODE_TYPE_REGISTER_VERIFY_EMAIL = 5;
	const CODE_TYPE_LOGIN_VERIFY_EMAIL = 6;

	/*zujuan insert register type*/
	const INSERT_REGISTER_EMAIL = 1;
	const INSERT_REGISTER_PHONE = 2;
	const INSERT_REGISTER_STUID = 3;
	const INSERT_REGISTER_UNAME = 4;

	/*zujuan send authcode interval*/
	const SEND_AUTHCODE_INTERVAL_EMAIL = "90 second";
	const SEND_AUTHCODE_INTERVAL_PHONE = "90 second";	
	const SEND_REDIS_AUTHCODE_INTERVAL_EMAIL = 90;//邮件短信发送间隔1分半钟
	const SEND_REDIS_AUTHCODE_INTERVAL_PHONE = 90;
	const SEND_REDIS_AUTHCODE_TIMES = 10;//每2分钟单个ip可以发送短信的次数，推广期暂定10次
	
	/*zujuan authcode expire time*/
	const AUTHCODE_EXPIRE_EMAIL = "24 hour";
	const AUTHCODE_EXPIRE_PHONE = "30 minute";
	const AUTHCODE_REDIS_EXPIRE_EMAIL = 86400;//邮件验证码有效时间，24小时
	const AUTHCODE_REDIS_EXPIRE_PHONE = 1800;//短信验证码有效时间，30分钟

	const DEFAULT_PER_PAGE = 10;

	/*zujuan qcount timeout*/
    const REDIS_AUTHLOGIN_TIMEOUT = 14400;//默认auto login用户名的缓存时间
    const REDIS_QCOUNT_TIMEOUT = 604800;//页面总数页面缓存的缓存时间，上线测试暂定24小时，上线后更改为7天
    const REDIS_INTELLIGENT_TIMEOUT = 604800;//智能选题结果保存七天

    function __construct()
	{
	
	}
}
/* End of file Constant.php */
/* Location: ./application/libraries/Constant.php */