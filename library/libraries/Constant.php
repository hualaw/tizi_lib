<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

Class CI_Constant {

	/*zujuan user type*/
	const USER_TYPE_ADMIN = 1;
	const USER_TYPE_STUDENT = 2;
	const USER_TYPE_TEACHER = 3;
	const USER_TYPE_PARENT = 4;
	const USER_TYPE_RESEARCHER = 5;

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
	const COOKIE_MYDIR_EXPIRE_TIME = 0;//MYDIR随浏览器

	const COOKIE_TZUSERNAME = "TZU";//自动登录cookie name
	const COOKIE_TZMYSUBJECT = "_ms";//mysubject cookie name
	const COOKIE_TZMYSUBJECT_PAPER = "_msp";//mysubject cookie name
	const COOKIE_TZMYSUBJECT_DOC = "_msd";//mysubject cookie name
	const COOKIE_TZMYSUBJECT_HOMEWORK = "_msh";//mysubject cookie name
	const COOKIE_TZTIPS = "_tp_";//tips cookie perfix name
	const COOKIE_NOREDIS = "_nrd";//no redis cookie name
	const COOKIE_INVITE = "invite";//invite cookie name
	const COOKIE_CURRENT_CLOUD_DIR = "_mdir";//cloud cookie name
	const COOKIE_TZMOBILE = "_mobile";//cloud cookie name

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

	/*tizi api type*/
	const API_TYPE_TIZI = 1;
	const API_TYPE_JXT = 2;
	const API_TYPE_AQ = 3;

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

    /*child binding*/
    const ONE_PARENT_BIND_KID_MAX =3; // 一个家长最多绑定的孩子数量
    const ONE_KID_IS_BINDED_MAX = 6; // 一个学生最多能被绑定的次数

	const DEFAULT_PER_PAGE = 10;
	const DEFAULT_PAGE_LIMIT = 100;

	/*zujuan qcount timeout*/
    const REDIS_AUTHLOGIN_TIMEOUT = 14400;//默认auto login用户名的缓存时间

	//medal types
	const TEACHER_AUTHENTICATION_MEDAL = 1;	//教师认证
	const USER_LOGIN_MEDAL = 2;				//登录达人
	const USER_REGISTER_MEDAL = 3;			//资深达人
	const USER_ACTIVITY_MEDAL = 4;			//活动认证

	const USER_MEDAL_TIMEOUT = 3600;		//勋章过期时间

    function __construct()
	{
	
	}

	public static function redirect_url($user_type, $redirect_type='login', $redirect_url='')
	{
		if(!$redirect_type) $redirect_type='login';
		$redirect_url = array(
			'login' => array(
				//self::USER_TYPE_STUDENT => tizi_url("student/home"),
				//self::USER_TYPE_TEACHER => login_url("teacher/user/center"),
				//self::USER_TYPE_PARENT => login_url("parent/user/center"),
				//self::USER_TYPE_RESEARCHER => login_url("researcher/user/center")
				self::USER_TYPE_STUDENT => tizi_url("student/home"),
			    self::USER_TYPE_TEACHER => tizi_url("teacher/class/my"),
			    self::USER_TYPE_PARENT => jia_url("parent/home"),
			    self::USER_TYPE_RESEARCHER => edu_url($redirect_url)
			),
			/*
			'register' => array(
				self::USER_TYPE_STUDENT => tizi_url("student/home"),
				self::USER_TYPE_TEACHER => login_url("teacher/user/center"),
				self::USER_TYPE_PARENT => login_url("parent/user/center"),
				self::USER_TYPE_RESEARCHER => login_url("researcher/user/center")
			),
			'tizi' => array(
				self::USER_TYPE_STUDENT => tizi_url("student/home"),
			    self::USER_TYPE_TEACHER => tizi_url(),
			    self::USER_TYPE_PARENT => jia_url("parent/home"),
			    self::USER_TYPE_RESEARCHER => edu_url($redirect_url)
			),
			'edu' => array(
				self::USER_TYPE_STUDENT => tizi_url("student/home"),
			    self::USER_TYPE_TEACHER => tizi_url(),
			    self::USER_TYPE_PARENT => jia_url("parent/home"),
			    self::USER_TYPE_RESEARCHER => edu_url($redirect_url)
			),
			*/
			'perfect' => array(
				self::USER_TYPE_STUDENT => login_url("student/user/perfect"),
			    self::USER_TYPE_TEACHER => login_url("teacher/user/perfect"),
			    self::USER_TYPE_PARENT => login_url("parent/user/perfect"),
			    self::USER_TYPE_RESEARCHER => login_url("researcher/user/perfect"),
			)
		);

		if(!isset($redirect_url[$redirect_type])) $redirect_url[$redirect_type] = $redirect_url['login'];

		return isset($redirect_url[$redirect_type][$user_type])?$redirect_url[$redirect_type][$user_type]:site_url();
	}
}
/* End of file Constant.php */
/* Location: ./application/libraries/Constant.php */
