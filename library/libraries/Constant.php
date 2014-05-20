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
	const REG_ORIGIN_CRM_STUID	= 43;
	const REG_ORIGIN_QQ_PERFECT	= 51;
	const REG_ORIGIN_QQ_SKIP	= 52;
	const REG_ORIGIN_WEIBO_PERFECT = 53;
	const REG_ORIGIN_WEIBO_SKIP = 54;
	const REG_ORIGEN_CLASS_EMAIL = 62;

	/*zujuan session and cookie expire*/
	const SESSION_EXPIRE_TIME = "2 hour";
	const COOKIE_EXPIRE_TIME = 0;//14400-4hour,0-with session expire
	const COOKIE_INVITE_EXPIRE_TIME = 86400;//邀请码过期时间24小时
	const COOKIE_REMEMBER_EXPIRE_TIME = 2592000;//30天免登录;//604800;//七天免登录
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
	const COOKIE_TZSUPPLY = "_sis";//skip information supply

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
	const CODE_TYPE_INVITE_EMAIL = 7; /*老师邀请注册发送邮件*/
	const CODE_TYPE_INVITE_PHONE = 8; /*老师邀请注册发送短信*/

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

    const DEFAULT_SUBJECT_ID = 2;//默认科目，初中数学
    const DEFAULT_GRADE_ID = 1;//默认年级，初中

	//medal types
	const TEACHER_AUTHENTICATION_MEDAL = 1;	//教师认证
	const USER_LOGIN_MEDAL = 2;				//登录达人
	const USER_REGISTER_MEDAL = 3;			//资深达人
	const USER_ACTIVITY_MEDAL = 4;			//活动认证

	const USER_MEDAL_TIMEOUT = 86400;		//勋章过期时间 24h

    function __construct()
	{
	
	}

	public static function redirect_url($user_type, $redirect_type='login', $redirect_url='')
	{
		if(!$redirect_type) $redirect_type='login';
		$redirect_url = array(
			'login' => array(
				self::USER_TYPE_STUDENT => tizi_url("student/home"),
			    self::USER_TYPE_TEACHER => tizi_url("teacher/cloud"),
			    self::USER_TYPE_PARENT => jia_url("parent/home"),
			    self::USER_TYPE_RESEARCHER => edu_url($redirect_url)
			),
			'logout' => array(
				self::USER_TYPE_STUDENT => tizi_url(),
			    self::USER_TYPE_TEACHER => tizi_url(),
			    self::USER_TYPE_PARENT => jia_url("parent/home"),
			    self::USER_TYPE_RESEARCHER => tizi_url()
			),
			'tizi' => array(
				self::USER_TYPE_STUDENT => tizi_url("student/home"),
			    self::USER_TYPE_TEACHER => tizi_url(),
			    self::USER_TYPE_PARENT => jia_url("parent/home"),
			    self::USER_TYPE_RESEARCHER => edu_url($redirect_url)
			),
			'supply' => array(
				self::USER_TYPE_STUDENT => login_url("student/user/supply"),
			    self::USER_TYPE_TEACHER => login_url("teacher/user/supply"),
			    self::USER_TYPE_PARENT => login_url("parent/user/supply"),
			    self::USER_TYPE_RESEARCHER => login_url("researcher/user/supply"),
			)
		);

		$redirect_url['register']=$redirect_url['login'];
		$redirect_url['edu']=$redirect_url['tizi'];
		if(!isset($redirect_url[$redirect_type])) $redirect_url[$redirect_type] = $redirect_url['login'];

		return isset($redirect_url[$redirect_type][$user_type])?$redirect_url[$redirect_type][$user_type]:site_url();
	}

	/** 用户使用的应用对应的值s
	 * @static
	 * @param $app_name
	 * @return mixed
	 */
	public static function user_apps_binary($app_name){
		$arr = array(
			'tiku' => 1,
			'xuetang' => 10
		);
		return isset($arr[$app_name]) ? $arr[$app_name] : $arr['tiku'];
	}
	
	public static function school_type(){
		$define = array(
			1 => "公立小学",
			2 => "公立中学",
			3 => "公立九年一贯制",
			8 => "公立十二年一贯制",
			4 => "私立小学",
			5 => "私立中学",
			6 => "私立九年一贯制",
			9 => "私立十二年一贯制",
			7 => "培训机构"
		);
		return $define;
	}

	public static function relation($id=false){
		$relation = array(
			1 => '爸爸',
			2 => '妈妈',
			3 => '其他'
		);
		return isset($relation[$id])?$relation[$id]:$relation;
	}

}
/* End of file Constant.php */
/* Location: ./application/libraries/Constant.php */
