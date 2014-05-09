<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Controller extends CI_Controller{

	protected $site='';

	protected $tizi_uid=0;
	protected $tizi_utype=0;
	protected $tizi_urname='';
	protected $tizi_stuid=0;

	protected $tizi_ursubject=0;
	protected $tizi_urgrade=0;
	protected $tizi_urdomain='';
	protected $tizi_avatar=0;
	protected $tizi_cert=0;
	protected $tizi_redirect='';

	protected $tizi_ajax=false;
	protected $tizi_mobile=false;
	protected $tizi_debug=false;
	protected $need_password=false;
	protected $user_constant=array();

	protected $_segmenttype=array('n','an','r','ar');
	protected $_segment=array('n'=>'','an'=>'','r'=>'','ar'=>'');

	protected $_loginlist=array();
	protected $_unloginlist=array();
	protected $_dnloginlist=array();
	protected $_captchalist=array();
	protected $_postlist=array();

	protected $_errormsg='';
	protected $_username='';
	protected $_page_name='';
	protected $_captcha_name='';
	protected $_callback_name='';

	protected $_check_login=true;
	protected $_check_token=true;
	protected $_check_captcha=true;
	protected $_check_post=true;

	public function __construct($site='')
	{
		parent::__construct();

		$this->site=$site;
		$this->auto_login();
		$this->init();
		$this->token_list();
		$this->request_check();
		$this->token();
		$this->load_smarty();
	}

	protected function init()
	{
		$this->tizi_uid=$this->session->userdata("user_id");
        $this->tizi_utype=$this->session->userdata("user_type");
		$this->tizi_urname=$this->session->userdata('urname');
		$this->tizi_stuid=$this->session->userdata("student_id");
		
        $this->tizi_ursubject=$this->session->userdata("register_subject");
        $this->tizi_urgrade=$this->session->userdata("register_grade");
        $this->tizi_urdomain=$this->session->userdata("register_domain");
		$this->tizi_avatar=$this->session->userdata("avatar");
		$this->tizi_cert=$this->session->userdata("certification");

		$this->load->library('user_agent');
		$this->tizi_mobile=(($this->agent->is_mobile()&&$this->input->cookie(Constant::COOKIE_TZMOBILE) !== '0')
			|| $this->input->cookie(Constant::COOKIE_TZMOBILE))?1:0;

		$this->_segment['n']=$this->uri->uri_string();
		$segment=$this->uri->segment_array();
        $this->_segment['an']=isset($segment[1])?$segment[1]:'';
        $this->_segment['r']=$this->uri->ruri_string();
        $segment=$this->uri->rsegment_array();
        $this->_segment['ar']=isset($segment[1])?$segment[1]:'';
        $this->_errormsg=$this->session->flashdata('errormsg');

        $this->tizi_redirect=redirect_url($this->tizi_utype,$this->site);

		$this->tizi_ajax=$this->input->is_ajax_request();

		$this->user_constant = array(
   			'user_type_student'=>Constant::USER_TYPE_STUDENT,
   			'user_type_teacher'=>Constant::USER_TYPE_TEACHER,
   			'user_type_parent'=>Constant::USER_TYPE_PARENT,
   			'user_type_researcher'=>Constant::USER_TYPE_RESEARCHER,
   			'user_type'=>array(
				Constant::USER_TYPE_STUDENT=>'student',
				Constant::USER_TYPE_TEACHER=>'teacher',
				Constant::USER_TYPE_PARENT=>'parent',
				Constant::USER_TYPE_RESEARCHER=>'researcher'
			),
			'role_name'=>array(
				Constant::USER_TYPE_STUDENT=>'学生',
				Constant::USER_TYPE_TEACHER=>'老师',
				Constant::USER_TYPE_PARENT=>'家长',
				Constant::USER_TYPE_RESEARCHER=>'教研员'
			)
   		);
   		$this->tizi_role=isset($this->user_constant['user_type'][$this->tizi_utype])?
   			$this->user_constant['user_type'][$this->tizi_utype]:'';
	}


	protected function load_smarty()
	{
        $base_url=base_url();
        $site_url=site_url();
        $tizi_url=tizi_url();
        $login_url=login_url();
        $edu_url=edu_url();
        $jxt_url=jxt_url();
        $zl_url=zl_url();
        $jia_url=jia_url();
        $xue_url=xue_url();
        $survey_url=survey_url();
        $static_url=static_url($this->site);
        $static_base_url=static_url('base');

        $this->load->helper("img_helper");
        $avatar_url=$this->tizi_avatar?path2avatar($this->tizi_uid):'';

        $this->load->config('version');
        $this->smarty->assign('base_url', $base_url);
        $this->smarty->assign('site_url', $site_url);
        $this->smarty->assign('tizi_url', $tizi_url);
        $this->smarty->assign('login_url', $login_url);
        $this->smarty->assign('edu_url', $edu_url);
        $this->smarty->assign('jxt_url', $jxt_url);
        $this->smarty->assign('zl_url', $zl_url);
        $this->smarty->assign('jia_url', $jia_url);
        $this->smarty->assign('xue_url', $xue_url);
        $this->smarty->assign('survey_url', $survey_url);
        $this->smarty->assign('this_url',site_url($this->_segment['n']));

        $this->smarty->assign('tzid', $this->config->item('sess_cookie_name'));
        $this->smarty->assign('tzu', Constant::COOKIE_TZUSERNAME);
        $this->smarty->assign('is_mobile', $this->tizi_mobile);
        
        $this->smarty->assign('static_url', $static_url);
        $this->smarty->assign('static_base_url', $static_base_url);
        $this->smarty->assign('version','?v='.$this->config->item('version'));
        $this->smarty->assign('swfversion','?v='.$this->config->item('swfversion'));
        $this->smarty->assign('static_version',$this->config->item('static_version')
        	.($this->config->item('static_version')?'/':''));

        $this->smarty->assign('base_student', redirect_url(Constant::USER_TYPE_STUDENT,$this->site));
    	$this->smarty->assign('base_teacher', redirect_url(Constant::USER_TYPE_TEACHER,$this->site));
   		$this->smarty->assign('base_parent', redirect_url(Constant::USER_TYPE_PARENT,$this->site));
   		$this->smarty->assign('base_researcher', redirect_url(Constant::USER_TYPE_RESEARCHER,$this->site));

   		//$this->smarty->assign('login_student', redirect_url(Constant::USER_TYPE_STUDENT,'login'));
    	$this->smarty->assign('login_teacher', redirect_url(Constant::USER_TYPE_TEACHER,'login'));
   		//$this->smarty->assign('login_parent', redirect_url(Constant::USER_TYPE_PARENT,'login'));
   		//$this->smarty->assign('login_researcher', redirect_url(Constant::USER_TYPE_RESEARCHER,'login'));

   		$this->smarty->assign('home_student', redirect_url(Constant::USER_TYPE_STUDENT,'tizi'));
    	$this->smarty->assign('home_teacher', redirect_url(Constant::USER_TYPE_TEACHER,'tizi'));
   		//$this->smarty->assign('home_parent', redirect_url(Constant::USER_TYPE_PARENT,'tizi'));
   		$this->smarty->assign('home_researcher', redirect_url(Constant::USER_TYPE_RESEARCHER,'tizi'));

   		if (defined('ENVIRONMENT') && ENVIRONMENT == 'development')
   		{
   			$this->smarty->assign('home_zl', zl_url('zl/home'));
   			$this->smarty->assign('home_parent', redirect_url(Constant::USER_TYPE_PARENT,'tizi'));
   		}
   		else
   		{
   			$this->smarty->assign('home_zl', zl_url());
   			$this->smarty->assign('home_parent', jia_url());
   		}

		//是否有答疑权限，有的话就显示答疑tab
		$this->smarty->assign('aq_show',$this->session->userdata('aq_show'));

   		$this->smarty->assign('base_avatar', $avatar_url);
   		$this->smarty->assign('constant', $this->user_constant);
   		$this->smarty->assign('environment', ENVIRONMENT);

		//generate global user_name
        $user_name=$this->tizi_urname;
        if($user_name=='') $user_name="您好!!!";
        $this->smarty->assign('user_name',$user_name);
        $this->smarty->assign('user_type',$this->tizi_utype);
        $this->smarty->assign('user_stuid',$this->tizi_stuid);
        $this->smarty->assign('user_cert',$this->tizi_cert);

		//generate global errormsg
        if(!$this->_errormsg) $this->_errormsg="";
        $this->smarty->assign('errormsg',$this->_errormsg);
	}

	protected function auto_login()
	{
        $this->_username=$this->input->cookie(Constant::COOKIE_TZUSERNAME);
        $this->tizi_uid=$this->session->userdata("user_id");

		if(!$this->tizi_uid&&$this->_username)
		{
			$this->load->model("login/session_model");

			$expire_time=Constant::COOKIE_EXPIRE_TIME;
			$user_id=0;
			$this->load->model("redis/redis_model");
			if($this->redis_model->connect('auto_login'))
			{
				$login_value=json_decode($this->cache->get($this->_username));
				if(!empty($login_value))
				{
					$user_id=$login_value->user_id;
					$expire_time=$login_value->expire_time;
				}
				$this->session_model->clear_cookie();
				if($user_id)
				{	
           			$login_status=$this->session_model->generate_session($user_id);
					if($login_status)
					{
						$username=$this->encrypt->decode($this->_username);
						$username=substr($username,0,strlen($username)-10);
            			$this->session_model->generate_cookie($username,$user_id,$expire_time);
            			$this->session_model->clear_mscookie();
            		}
				}
			}
			else
			{
				$this->session_model->clear_cookie();
			}
		}
	}

	protected function request_check()
	{
		//强制转换成post提交，进行token验证
		if($this->_check_post)
		{
			$check_post=0;
			foreach($this->_segmenttype as $st)
			{
				if(!empty($this->_segment[$st])&&isset($this->_postlist[$st])&&!empty($this->_postlist[$st])&&in_array($this->_segment[$st],$this->_postlist[$st]))
				{
					$check_post++;
				}
			}
			if($check_post){
				if(empty($_POST))
				{
					$_POST=$_GET;
					$_GET=array();
				}
			}
		}
	}

	protected function token()
	{
		$this->_page_name=$this->input->post('page_name',true);
		$this->_captcha_name=$this->input->post('captcha_name',true,true,$this->_page_name);
		$this->_callback_name=$this->input->get_post('callback_name',true);

		$token=$this->input->post('token');
		$captcha=$this->input->post('captcha_word');

		//post 检测captcha
		if($this->_check_captcha)
		{
			$check_captcha=0;
			foreach($this->_segmenttype as $st)
			{
				if(!empty($this->_segment[$st])&&isset($this->_captchalist[$st])&&!empty($this->_captchalist[$st])&&in_array($this->_segment[$st],$this->_captchalist[$st]))
				{
					$check_captcha++;
				}
			}
			if($check_captcha)
			{
				$check_captcha=$this->captcha->validateCaptcha($captcha,$this->_captcha_name);
				if(!$check_captcha)
				{
					$_POST=array();
					if($this->_callback_name) $_POST['callback_name']=$this->_callback_name;
				}
			}
		}
		
		//post 检测token
		if($this->_check_token)
		{
			if($this->_page_name)
		    {
				$check_token=$this->page_token->check_csrf_token($this->_page_name,$token);
				if(!$check_token)
				{
					if($this->tizi_ajax)
					{
						log_message('trace_tizi','Token check failed',array('user_id'=>$this->tizi_uid,'page_name'=>$this->_page_name));
						echo json_ntoken(array('errorcode'=>false,'error'=>$this->lang->line('default_error_token'),'token'=>false,'code'=>1));
						exit();
					}
					else
					{
						$_POST=array();
						if($this->_callback_name) $_POST['callback_name']=$this->_callback_name;
					}
				}
			}
			else
			{
				$_POST=array();
				if($this->_callback_name) $_POST['callback_name']=$this->_callback_name;
			}
		}

		//检测未登录
		if($this->_check_login&&!empty($this->_segment['an']))
		{
			if(!$this->tizi_uid)
			{
				//上传，必须登录
				if($this->_segment['an'] == 'upload')
				{
					echo json_ntoken(array('errorcode'=>false,'error'=>$this->lang->line('default_error_login'),'msg'=>$this->lang->line('default_error_login'),'success'=>false,'login'=>false,'token'=>false,'code'=>1));
		            exit();
				}

				$check_login=0;
				foreach($this->_segmenttype as $st)
				{
					if(!empty($this->_segment[$st])&&isset($this->_unloginlist[$st])&&!empty($this->_unloginlist[$st])&&in_array($this->_segment[$st],$this->_unloginlist[$st]))
			        {
	            		$check_login++;
			        }
			    }
			    if(!$check_login)
			    {
			    	if($this->tizi_ajax)
					{
						$this->load->config('version');
						$this->smarty->assign('static_url', static_url($this->site));
				        $this->smarty->assign('static_version',$this->config->item('static_version')
				        	.($this->config->item('static_version')?'/':''));
						$login_redirect=$this->input->get_post('redirect',true,false,'reload');
						$reg_redirect=$this->input->get_post('reg_redirect',true);
						$reg_role=$this->input->get_post('reg_role',true);
						$this->smarty->assign('login_url',login_url());
						$this->smarty->assign('login_redirect',$login_redirect);
						$this->smarty->assign('reg_redirect',$reg_redirect);
						$this->smarty->assign('reg_role',$reg_role);
						$html=$this->smarty->fetch('[lib]header/tizi_login_form.html');
				    	echo json_ntoken(array('errorcode'=>false,'error'=>$this->lang->line('default_error_login'),'login'=>false,'html'=>$html,'token'=>false,'code'=>1));
					    exit();
					}
					else
					{
						//$this->session->set_flashdata('errormsg',$this->lang->line('default_error_login'));
			    		redirect(site_url('',$this->site));
			    	}
			    }
		    }
		    else
		    {
		    	$check_dnlogin=0;
				foreach($this->_segmenttype as $st)
				{
					if(!empty($this->_segment[$st])&&isset($this->_dnloginlist[$st])&&!empty($this->_dnloginlist[$st])&&in_array($this->_segment[$st],$this->_dnloginlist[$st]))
			        {
	            		$check_dnlogin++;
			        }
			    }
				if($check_dnlogin)
		        {
		            if($this->tizi_ajax) 
		            {
		            	$error=sprintf($this->lang->line('default_error_re_login'),$this->user_constant['role_name'][$this->tizi_utype]);
		                echo json_ntoken(array('errorcode'=>false,'error'=>$error,'redirect'=>$this->tizi_redirect,'dnlogin'=>true,'code'=>1));
		                exit();
		            }
		            else
		            {
		                redirect($this->tizi_redirect);
		            }
		        }
		    }
		}
	}

	protected function token_list()
	{
		//登录情况下才可以访问的页面
		$this->_loginlist=array('n'=>array(),'an'=>array(),'r'=>array(),'ar'=>array());
		//不登录情况下可以访问的页面
		$this->_unloginlist=array('n'=>array(),'an'=>array(),'r'=>array(),'ar'=>array());
		//登录情况下不可以访问的页面
		$this->_dnloginlist=array('n'=>array(),'an'=>array(),'r'=>array(),'ar'=>array());
		//必须经过验证码验证的请求
		$this->_captchalist=array('n'=>array(),'an'=>array(),'r'=>array(),'ar'=>array());
		//强制post的请求
		$this->_postlist=array('n'=>array(),'an'=>array(),'r'=>array(),'ar'=>array());
	}

}

/* End of file LI_Controller.php */
/* Location: ./library/core/LI_Controller.php */
