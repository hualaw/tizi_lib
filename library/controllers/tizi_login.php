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
			
			$submit['redirect']=$this->get_login_redirect($user_id['user_type'],$session['user_data'],$redirect_type);
			$submit['errorcode']=true;
		}
		else if($user_id['errorcode'] != Constant::LOGIN_INVALID_TYPE)
		{
			//每次重新登陆临时帐号需要重置session
			$this->session->unset_userdata("cretae_pk");
			$this->session->unset_userdata("user_invite_id");
			
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
						$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
					}
				}
				else
				{
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
						$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
					}
				}
				else
				{
					$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
				}
			}
			else
			{
				$submit['error']=$this->lang->line('error_'.strtolower($user_id['error']));
			}
		}
		else
		{
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
    	$redirect=$this->input->post('redirect',true);
    	$html='';
        $errorcode=($this->tizi_uid>0);
        if(!$errorcode)
        {
	        $this->smarty->assign('login_url',login_url());
			$this->smarty->assign('redirect',$redirect);
			$html=$this->smarty->fetch('[lib]header/tizi_login_form.html');
		}
        echo json_token(array('errorcode'=>$errorcode,'html'=>$html));
        exit();
    }

	private function check_captcha()
    {
        $errorcode=false;
        echo json_token(array('errorcode'=>$errorcode));
        exit();
    }

    private function get_redirect($user_type,$user_data,$redirect_type)
   	{
   		$redirect=redirect_url($user_type,$redirect_type);
   		switch ($user_type) 
		{
			case Constant::USER_TYPE_STUDENT: 	if(!!$user_data['uname'] || !$user_data['register_grade'])
												{
													$redirect=redirect_url(Constant::USER_TYPE_STUDENT,'perfect');
												}
												else if($this->tizi_invite) 
												{
													$redirect=tizi_url("invite/".$this->tizi_invite);
												}
												break;
            case Constant::USER_TYPE_TEACHER: 	if(!$user_data['register_subject']) 
            									{
            										$redirect=redirect_url(Constant::USER_TYPE_TEACHER,'perfect');
            									}
            									else if($this->tizi_invite) 
            									{
            										$redirect=tizi_url("invite/".$this->tizi_invite);
            									}
            									break;
            case Constant::USER_TYPE_PARENT:	
            case Constant::USER_TYPE_RESEARCHER:
            default:							$redirect=redirect_url($user_type,$redirect_type);
            									break;
		}
		return $redirect;
   	}

   	private function get_login_redirect($user_type,$user_data,$redirect_type)
   	{
   		if(strpos('http://',$redirect_type)!==false)
		{
			$redirect=$redirect_type;
		}
		else if($redirect_type==='none')
		{
			$redirect='';
		}
		else if($redirect_type==='reload')
		{
			$redirect='reload';
		}
		else
		{
			$redirect=$this->get_redirect($user_type,$user_data,$redirect_type);
		}
		return $redirect;
   	}

}	
/* End of file login.php */
/* Location: ./application/controllers/login/login.php */
