<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class LI_Controller extends CI_Controller{

	protected $site='';

	protected $tizi_uid=0;
	protected $tizi_utype=0;
	protected $tizi_uname='';
	protected $tizi_urname='';

	protected $tizi_ursubject=0;
	protected $tizi_urgrade=0;
	protected $tizi_avatar=0;
	protected $tizi_invite='';
	protected $tizi_redirect='';

	protected $tizi_ajax=false;
	protected $tizi_debug=false;

	protected $_loginlist=array();
	protected $_captchalist=array();
	protected $_postlist=array();

	protected $_segment=array();
	protected $_rsegment='';
	protected $_errormsg='';
	protected $_username='';
	protected $_page_name='';
	protected $_captcha_name='';

	public function __construct($site='')
	{
		parent::__construct();

		$this->site=$site;
		$this->init();
		$this->auto_login();
		$this->token_list();
		$this->request_check();
		$this->token();
		$this->load_smarty();
	}

	protected function init()
	{
		$this->tizi_uid=$this->session->userdata("user_id");
        $this->tizi_utype=$this->session->userdata("user_type");
        $this->tizi_uname=$this->session->userdata("uname");
		$this->tizi_urname=$this->session->userdata('urname');
		$this->tizi_avatar=$this->session->userdata("avatar");

        $this->_segment=$this->uri->segment_array();
        $this->_rsegment=$this->uri->ruri_string();
        $this->_errormsg=$this->session->flashdata('errormsg');

        $this->tizi_redirect=get_redirect($this->tizi_utype);
		$this->tizi_ajax=$this->input->is_ajax_request();
	}


	protected function load_smarty()
	{
        $base_url=base_url();
        $site_url=site_url();
        $tizi_url=tizi_url();
        $login_url=login_url();
        $vip_url=vip_url();
        $jxt_url=jxt_url();
        $static_url=static_url($this->site);

        $this->load->helper("img_helper");
        $avatar_url=$this->tizi_avatar?path2avatar($this->tizi_uid):'';

        $this->load->config('version');
        $this->smarty->assign('base_url', $base_url);
        $this->smarty->assign('site_url', $site_url);
        $this->smarty->assign('tizi_url', $tizi_url);
        $this->smarty->assign('login_url', $login_url);
        $this->smarty->assign('vip_url', $vip_url);
        $this->smarty->assign('jxt_url', $jxt_url);

        $this->smarty->assign('tzid', $this->config->item('sess_cookie_name'));
        $this->smarty->assign('tzu', Constant::COOKIE_TZUSERNAME);
        
        $this->smarty->assign('static_url', $static_url);
        $this->smarty->assign('static_base_url', $base_url.'application/views/static/');
        $this->smarty->assign('version','?v='.$this->config->item('version'));
        $this->smarty->assign('swfversion','?v='.$this->config->item('swfversion'));

        $this->smarty->assign('base_student', $site_url.Constant::REDIRECT_STUDENT);
    	$this->smarty->assign('base_teacher', $site_url.Constant::REDIRECT_TEACHER);
   		$this->smarty->assign('base_parent', $site_url.Constant::REDIRECT_PARENT);
   		$this->smarty->assign('base_researcher', $site_url.Constant::REDIRECT_RESEARCHER);
   		$this->smarty->assign('base_avatar', $avatar_url);

   		$this->smarty->assign('constant', array(
   			'user_type_student'=>Constant::USER_TYPE_STUDENT,
   			'user_type_teacher'=>Constant::USER_TYPE_TEACHER,
   			'user_type_parent'=>Constant::USER_TYPE_PARENT,
   			'user_type_researcher'=>Constant::USER_TYPE_RESEARCHER
   			)
   		);

		//generate global user_name
        $user_name=$this->tizi_urname;
        if($user_name=='') $user_name="您好!!!";
        $this->smarty->assign('user_name',$user_name);
        $this->smarty->assign('user_type',$this->tizi_utype);

		//generate global errormsg
        if(!$this->_errormsg) $this->_errormsg="";
        $this->smarty->assign('errormsg',$this->_errormsg);
	}

	protected function auto_login()
	{
        $this->_username=$this->input->cookie(Constant::COOKIE_TZUSERNAME);

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
		if(!empty($this->_rsegment)&&in_array($this->_rsegment,$this->_postlist))
		{
			if(empty($_POST))
			{
				$_POST=$_GET;
				$_GET=array();
			}
		}
	}

	protected function token()
	{
		$this->_page_name=$this->input->post('page_name');
		$this->_captcha_name=$this->input->post('captcha_name');
		if(!$this->_captcha_name) $this->_captcha_name=$this->_page_name;

		$token=$this->input->post('token');
		$captcha=$this->input->post('captcha_word');

		if(!empty($this->_rsegment)&&in_array($this->_rsegment,$this->_captchalist))
		{
			$check_captcha=$this->captcha->validateCaptcha($captcha,$this->_captcha_name);
			if(!$check_captcha)
			{
				$_POST=array();
			}
		}

		//ajax请求，带page_name表示为post ajax请求
		if($this->tizi_ajax)
		{
			//检测未登录ajax
			if(!$this->tizi_uid)
			{
				if(!empty($this->_segment) && !in_array($this->_segment[1],$this->_loginlist))
		        {
		            echo json_ntoken(array('errorcode'=>false,'error'=>$this->lang->line('default_error_login'),'login'=>false,'token'=>false,'code'=>1));
		            exit();
		        }
		    }

		    //post 检测token
		    if($this->_page_name)
		    {
				$check_token=$this->page_token->check_csrf_token($this->_page_name,$token);
				if(!$check_token)
				{
					log_message('trace_tizi','Token check failed',array('user_id'=>$this->tizi_uid,'page_name'=>$this->_page_name));
					echo json_ntoken(array('errorcode'=>false,'error'=>$this->lang->line('default_error_token'),'token'=>false,'code'=>1));
					exit();
				}
			}
			else
			{
				$_POST=array();
			}
		}
		//普通页面
		else
		{
			if($this->_page_name)
		    {
				$check_token=$this->page_token->check_csrf_token($this->_page_name,$token);
				if(!$check_token)
				{
					$_POST=array();
				}
			}
			else
			{
				$_POST=array();
			}

			//检测未登录
			if(!$this->tizi_uid)
			{
				//上传
				if(!empty($this->_segment) && $this->_segment[1] == 'upload')
				{
					echo json_ntoken(array('errorcode'=>false,'error'=>$this->lang->line('default_error_login'),'success'=>false,'login'=>false,'token'=>false,'code'=>1));
		            exit();
				}
				else if(!empty($this->_segment) && !in_array($this->_segment[1],$this->_loginlist))
		        {
					//$this->session->set_flashdata('errormsg',$this->lang->line('default_error_login'));
            		redirect('');
		        }
		    }
		    else
		    {
		    	$this->binding();
		    }
		}
	}

	protected function binding()
	{
		return;
	}

	protected function token_list()
	{
		//不登陆情况下可以使用的ajax请求
		$this->_loginlist=array();
		//必须经过验证码验证的请求
		$this->_captchalist=array();
		//强制post的请求
		$this->_postlist=array();
	}

}

/* End of file LI_Controller.php */
/* Location: ./library/core/LI_Controller.php */
