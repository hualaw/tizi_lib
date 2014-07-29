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
	const REG_ORIGIN_WEB_PHONE = 1;
	const REG_ORIGIN_WEB_EMAIL = 2;
	const REG_ORIGIN_WEB_STUID = 3;
	const REG_ORIGIN_WEB_UNAME = 4;
	//app
	const REG_ORIGIN_AQ_IOS = 21;
	const REG_ORIGIN_AQ_ANDROID = 31;
	const REG_ORIGIN_APP_IOS = 22;
	const REG_ORIGIN_APP_IOS_QQ = 23;
	const REG_ORIGIN_APP_IOS_WEIBO = 24;
	const REG_ORIGIN_IOS_TIKU = 25;
	const REG_ORIGIN_IOS_TIKU_QQ = 26;
	const REG_ORIGIN_IOS_TIKU_WEIBO = 27;
	const REG_ORIGIN_IOS_JXT = 28;
	const REG_ORIGIN_APP_ANDROID = 32;
	const REG_ORIGIN_APP_ANDROID_QQ = 33;
	const REG_ORIGIN_APP_ANDROID_WEIBO = 34;
	const REG_ORIGIN_ANDROID_TIKU = 35;
	const REG_ORIGIN_ANDROID_TIKU_QQ = 36;
	const REG_ORIGIN_ANDROID_TIKU_WEIBO = 37;
	const REG_ORIGIN_ANDROID_JXT = 38;
	//crm
	const REG_ORIGIN_CRM = 41;
	const REG_ORIGIN_CRM_STUID	= 43;
	//sso
	const REG_ORIGIN_QQ_PERFECT	= 51;		//第三方登录-qq-完善信息
	const REG_ORIGIN_QQ_SKIP	= 52;		//第三方登录-qq-跳过
	const REG_ORIGIN_WEIBO_PERFECT = 53;	//第三方登录-weibo-完善信息
	const REG_ORIGIN_WEIBO_SKIP = 54;		//第三方登录-weibo-跳过
	const REG_ORIGIN_WEIXIN_PERFECT = 55;	//第三方登录-weixin-完善信息
	const REG_ORIGIN_WEIXIN_SKIP = 56;		//第三方登录-weixin-跳过
	const REG_ORIGIN_TADD_PERFECT = 58;		//班级老师添加的学生帐号-完善信息
	const REG_ORIGIN_TADD_SKIP = 59;		//班级老师添加的学生帐号-跳过
	//classcode
	const REG_ORIGIN_CLASS_EMAIL = 62;
	const REG_ORIGIN_CLASS_UNAME = 64;
	//sso
	const REG_ORIGIN_SSO_PERFECT  = 70;		//SSO厂商登录-完善信息
	const REG_ORIGIN_SSO_SKIP = 71;			//SSO厂商登录-跳过
	const REG_ORIGIN_CARD_PERFECT = 72;		//卡片登录-完善信息
	const REG_ORIGIN_CARD_SKIP = 73;		//卡片登录-跳过
	//app dafen
	const REG_ORIGIN_ANDROID_DAFEN = 80;
	const REG_ORIGIN_IOS_DAFEN = 90;


	const QRTOKEN_EXPIRE_TIME = 300;//14400-4hour,0-with session expire
	const REDIS_QRTOKEN_TIMEOUT = 300;//二维码登录token有效期

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
	const COOKIE_TZSUPPLY_EXPIRE_TIME = 31536000;//skip information supply

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
	const COOKIE_PARENT_AREA = "_jia";//家长端地区
	const COOKIE_STUDENT_PK = "_pk";//专项挑战
	const COOKIE_SCHOOL_LOGIN = "_schl";//学校真实姓名登录记录的school_id

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
	
	/*login sso type*/
	const LOGIN_SSO_TYPE_OAUTH = 1;		//OAUTH 第三方登录
	const LOGIN_SSO_TYPE_SSO = 2;		//SSO	厂商登录
	const LOGIN_SSO_TYPE_CARD = 3;		//梯子帐号卡登录
	const LOGIN_SSO_TYPE_TADD = 4;		//老师添加的帐号

	/*tizi api type*/
	const API_TYPE_TIZI = 1;
	const API_TYPE_JXT = 2;
	const API_TYPE_AQ = 3;
	const API_TYPE_TIKU = 4;
	const API_TYPE_DAFEN = 5;

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
    const DEFAULT_SUBJECT_TYPE = 2;//数学
    const DEFAULT_GRADE_ID = 1;//默认年级，初中

	//medal types
	const TEACHER_AUTHENTICATION_MEDAL = 1;	//教师认证
	const USER_LOGIN_MEDAL = 2;				//登录达人
	const USER_REGISTER_MEDAL = 3;			//资深达人
	const USER_ACTIVITY_MEDAL = 4;			//活动认证

	const USER_MEDAL_TIMEOUT = 86400;		//勋章过期时间 24h

	const CLASS_STORAGE = 10737418240;//班级分享文件总容量 10GB = 10*1024*1024*1024
 
	const USER_DISTINCT_MEDAL_COUNT_TIMEOUT = 3600;		// 1h
	
	//about devote
	const DEVOTE_LESSON_SHARE = 19;			//文件共享贡献
	
	//about credit
	const CREDIT_STORE_MAXBUY_PERDAY = 3;	//积分商城一天最大兑换次数

    function __construct()
	{
	
	}

	public static function redirect_url($user_type, $redirect_type='login', $redirect='')
	{
		if(!$redirect_type) $redirect_type='login';
		$redirect_url = array(
			'login' => array(
				self::USER_TYPE_STUDENT => login_url("student/user/center"),
			    self::USER_TYPE_TEACHER => login_url("teacher/user/center"),
			    self::USER_TYPE_PARENT => jia_url()
			),
			'logout' => array(
				self::USER_TYPE_STUDENT => tizi_url(),
			    self::USER_TYPE_TEACHER => tizi_url(),
			    self::USER_TYPE_PARENT => jia_url()
			),
			'register' => array(
				self::USER_TYPE_STUDENT => tizi_url("ban"),
			    self::USER_TYPE_TEACHER => tizi_url("banji"),
			    self::USER_TYPE_PARENT => jia_url()
			),
			'tizi' => array(
				self::USER_TYPE_STUDENT => tizi_url('xuesheng'),
			    self::USER_TYPE_TEACHER => tizi_url('laoshi'),
			    self::USER_TYPE_PARENT => tizi_url('jiazhang')
			)
		);

		//$redirect_url['register']=$redirect_url['login'];
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
			'xuetang' => 10,
			'dafen' => 100
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

    public static function oauth_platform($platform){
        
        $platforms = array(
            1 => 'qq',
            2 => 'weibo',
            3 => 'weixin',
        );

        return array_search($platform, $platforms);
        
    }

	/* 教师空间配置 以前在space下 拿到lib 开通空间用 */
	const SPACE_SUBSCRIBE_ACCOUNT=10000;
	const SPACE_ARTICLE_TITLE='欢迎您在梯子网安家';
	const SPACE_TIZI_NICKNAME='梯子网空间';
	const SPACE_TIZI_DOMAIN='tizi';
	const SPACE_TIZI_AVATAR='default_avatar.gif';
	const SPACE_ARTICLE_CONTENT = '
亲爱的老师：<br>
欢迎您在梯子网安家   <br>
您可以用文字、图片记录和展示最真实的自我，与其他老师交流，随时随地记录教学感悟和趣闻。<br>
  <br>
准备好了吗？现在就开始精彩的空间之旅！ <br>
    <br>
温馨提示：   <br>
多关注其他老师，便可第一时间看到更多的内容。<br>
文章可以添加附件，方便您把相关资源提供给大家。<br>
<br>
这样做您的空间会受到更多的关注：<br>
多写文章，向大家分享自己的文字；<br>
完善个人资料，上传靓照当头像，向大家介绍自己<br>
随便逛逛，看看邻居的观点，留下您的宝贵评论<br>
<br>
如果有问题，可以向梯子网进行<a href="http://www.tizi.com/about/feedback" target="_blank">反馈</a> ，我们第一时间给您回复。<br>
<br>
梯子网
<br>  ';

	public static function get_content($space_user_id){
		$string = '
			亲爱的老师：<br>
			欢迎您在梯子网安家，您的博客地址是：
			<a href="'.site_url().'space/'.$space_user_id.'" target="_blank" style="color:#009a83">'.site_url().'space/'. $space_user_id.' </a><br>
			您可以用文字、图片记录和展示最真实的自我，与其他老师交流，随时随地记录教学感悟和趣闻。<br>
			  <br>
			准备好了吗？现在就开始精彩的空间之旅！ <br>
			    <br>
			温馨提示：   <br>
			多关注其他老师，便可第一时间看到更多的内容。<br>
			文章可以添加附件，方便您把相关资源提供给大家。<br>
			<br>
			这样做您的空间会受到更多的关注：<br>
			多<a href="'.site_url().'space/'.$space_user_id.'/add" target="_blank" style="color:#009a83">写文章</a>，向大家分享自己的文字；<br>
			<a href="'.site_url().'myspace/settings" target="_blank" style="color:#009a83">完善个人资料</a>，上传靓照当头像，向大家介绍自己<br>
			随便逛逛，看看邻居的观点，留下您的宝贵评论<br>
			<br>
			如果有问题，可以向梯子网进行<a href="http://www.tizi.com/about/feedback" target="_blank" style="color:#009a83">反馈</a> ，我们第一时间给您回复。<br>
			<br>
			梯子网
			<br>  ';
		return $string;
	}

}
/* End of file Constant.php */
/* Location: ./application/libraries/Constant.php */
