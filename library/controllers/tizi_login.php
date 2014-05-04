<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tizi_Login extends MY_Controller {
	
	private $_smarty_dir="login/";

    function __construct()
    {
        parent::__construct();

		$this->load->model("login/login_model");
		$this->load->model("login/session_model");
    }

    public function submit()
	{
		$username=$this->input->post("username",true);
		$password=$this->input->post("password",true);
		$redirect_type=$this->input->post("redirect",true,false,'login');
		$redirect_url=$this->input->post("redirect_url",true,false,'');
		if($redirect_type=='homepage')
		{
			$redirect_url='';
			$redirect_type='login';
		}
		if(stripos($redirect_type,'http://')!==false)
		{
			$redirect_url=$redirect_type;
			$redirect_type='login';
		}

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$user_id=$this->login_model->login($username,$password);
		if($user_id['errorcode']==Constant::LOGIN_SUCCESS)
		{
			$remember=$this->input->post('remember',true);
			if($remember) $cookie_time=Constant::COOKIE_REMEMBER_EXPIRE_TIME;
			else $cookie_time=Constant::COOKIE_EXPIRE_TIME;
			$session=$this->session_model->generate_session($user_id['user_id']);
			$this->session_model->generate_cookie($username,$user_id['user_id'],$cookie_time);
			$this->session_model->clear_mscookie();
			if($user_id['error']) $submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
			
			$submit['redirect']=$this->get_login_redirect($user_id['user_type'],$session['user_data'],$redirect_type,$redirect_url);
			$submit['errorcode']=true;
		}
		else if($user_id['errorcode'] != Constant::LOGIN_INVALID_TYPE)
		{
			//每次重新登录临时帐号需要重置session
			$this->session->unset_userdata("cretae_pk");
			$this->session->unset_userdata("user_invite_id");

			//完善信息后跳转页面
			if(stripos($redirect_url,'http://')!==false)
			{
				$this->session->set_userdata('perfect_redirect',$redirect_url);
			}
			
			if (preg_phone($username))
			{
				$this->load->model("login/user_invite_model");
				$user_invite = $this->user_invite_model->login($username, $password, "phone", true);
				if ($user_invite["id"] > 0)
				{
					if ($user_invite["user_type"] == Constant::USER_TYPE_PARENT)
					{
						$this->session->set_userdata("user_invite_id", $user_invite["id"]);
						$submit["redirect"] = login_url("register/perfect_parent");
						$submit["errorcode"] = true;
					}
					else 
					{
						log_message('trace_tizi','23800011:login failed:'.$username.':'.$password,$user_id);
						$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
					}
				}
				else
				{
					log_message('trace_tizi','23800012:login failed:'.$username.':'.$password,$user_id);
					$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
				}
			}
			else if (preg_stuid($username))
			{
				$this->load->model("class/classes_student_create");
				$create_pk = $this->classes_student_create->login($username, $password);
				if ($create_pk > 0)
				{
					$this->session->set_userdata("create_pk", $create_pk);
					$submit["redirect"] = login_url("register/student_sign");
					$submit["errorcode"] = true;
				}
				else
				{
					//如果classes_student_create表没有则查询user_invite
					$this->load->model("login/user_invite_model");
					$user_invite = $this->user_invite_model->login($username, $password, "student_id", true);
					if (isset($user_invite) && $user_invite["user_type"] == Constant::USER_TYPE_STUDENT){
						$this->session->set_userdata("user_invite_id", $user_invite["id"]);
						$submit["redirect"] = login_url("register/perfect_student");
						$submit["errorcode"] = true;
					} else {
						log_message('trace_tizi','23800013:login failed:'.$username.':'.$password,$user_id);
						$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
					}
				}
			}
			else if (preg_uname($username))
			{
				$this->load->model("login/user_invite_model");
				$user_invite = $this->user_invite_model->login($username, $password, "uname", true);
				if ($user_invite["id"] > 0)
				{
					if ($user_invite["user_type"] == Constant::USER_TYPE_TEACHER)
					{
						$this->session->set_userdata("user_invite_id", $user_invite["id"]);
						$submit["redirect"] = login_url("register/perfect_teacher");
						$submit["errorcode"] = true;
					} 
					else if ($user_invite["user_type"] == Constant::USER_TYPE_RESEARCHER)
					{
						$this->session->set_userdata("user_invite_id", $user_invite["id"]);
						$submit["redirect"] = login_url("register/perfect_researcher");
						$submit["errorcode"] = true;
					}
					else 
					{
						log_message('trace_tizi','23800014:login failed:'.$username.':'.$password,$user_id);
						$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
					}
				}
				else
				{
					log_message('trace_tizi','23800015:login failed:'.$username.':'.$password,$user_id);
					$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
				}
			}
			else
			{
				log_message('trace_tizi','23800016:login failed:'.$username.':'.$password,$user_id);
				$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
			}
		}
		else
		{
			log_message('trace_tizi','23800017:login failed:'.$username.':'.$password,$user_id);
			$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
		}
		echo json_token($submit);
    	exit();
	}

	function logout($site='')
	{
		$this->session_model->clear_session();
		$this->session_model->clear_cookie();
		$this->session_model->clear_current_dir_cookie();
		redirect(site_url('',$site));
	}
	
	function check_code()
	{
		$phone=$this->input->post('phone',true);		
		$authcode=$this->input->post('check_code',true);
		$code_type=$this->input->post('code_type',true);

		$error='';
		
		$this->load->model("login/verify_model");
    	$auth=$this->verify_model->verify_authcode_phone($authcode,$phone,false);
	
		if($auth['errorcode'])
		{
			$errorcode=true;
		}
		else 
		{
			$errorcode=false;	
			$error=$this->lang->line('error_sms_code');
		}

		echo json_token(array('errorcode'=>$errorcode,'error'=>$error));
		exit();
	}

	public function check_login()
    {
    	$redirect=$this->input->get('redirect',true);
    	$reg_redirect=$this->input->get('href',true);
    	$reg_role=$this->input->get('role',true);
    	$nohtml=$this->input->get('nohtml',true,false,0);
    	$html='';
        $errorcode=($this->tizi_uid>0);
        if(!$errorcode)
        {
        	if(strpos($redirect,'http://')!==false) $reg_redirect=$redirect;
	        $this->smarty->assign('login_url',login_url());
			$this->smarty->assign('login_redirect',$redirect);
			$this->smarty->assign('reg_redirect',$reg_redirect);
			$this->smarty->assign('reg_role',$reg_role);
			if(!$nohtml) $html=$this->smarty->fetch('[lib]header/tizi_login_form.html');
			$redirect='';
		}
        echo json_token(array('errorcode'=>$errorcode,'html'=>$html,'redirect'=>$redirect,'reg_redirect'=>$reg_redirect,'reg_role'=>$reg_role));
        exit();
    }

    public function check_logout()
    {
    	$redirect=$this->input->get('redirect',true);
        $errorcode=($this->tizi_uid>0);
        if($errorcode)
        {
        	$this->session_model->clear_session();
			$this->session_model->clear_cookie();
			$this->session_model->clear_current_dir_cookie();
        	if($redirect != 'reload'&&strpos($redirect,'http://')===false) $redirect='';
		}
        echo json_token(array('errorcode'=>$errorcode,'redirect'=>$redirect));
        exit();
    }

	private function check_captcha()
    {
        $errorcode=false;
        echo json_token(array('errorcode'=>$errorcode));
        exit();
    }

    protected function get_redirect($user_type,$user_data,$redirect_type,$redirect_url=false)
   	{
   		$redirect=redirect_url($user_type,$redirect_type);
   		switch ($user_type) 
		{
			case Constant::USER_TYPE_STUDENT:
				if(!$user_data['uname'] || !$user_data['register_grade'])
				{
					$redirect=redirect_url(Constant::USER_TYPE_STUDENT,'perfect');
					$redirect.='?redirect='.urlencode($redirect_url);
				}
				break;
            case Constant::USER_TYPE_TEACHER:
            	if(!$user_data['register_subject'])
				{
					$redirect=redirect_url(Constant::USER_TYPE_TEACHER,'perfect');
					$redirect.='?redirect='.urlencode($redirect_url);
				}
				break;
            case Constant::USER_TYPE_PARENT:	
            case Constant::USER_TYPE_RESEARCHER:
            default:
            	if($redirect_url) $redirect=$redirect_url;
            	else $redirect=redirect_url($user_type,$redirect_type);
            	break;
		}
		return $redirect;
   	}

   	private function get_login_redirect($user_type,$user_data,$redirect_type,$redirect_url=false)
   	{
   		if($redirect_type==='none')
		{
			$redirect='';
		}
		else if($redirect_type==='reload' || stripos($redirect_type,'callback:')!==false || $redirect_type==='function')
		{
			$redirect=$redirect_type;
		}
		else if(stripos($redirect_type,'http://')!==false)
		{
			$redirect=$this->get_redirect($user_type,$user_data,'login',$redirect_type);
		}
		else//login
		{
			$redirect=$this->get_redirect($user_type,$user_data,$redirect_type,$redirect_url);
		}
		return $redirect;
   	}

}	
/* End of file login.php */
/* Location: ./application/controllers/login/login.php */
