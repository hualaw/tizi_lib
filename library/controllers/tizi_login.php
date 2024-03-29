<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once("tizi_controller.php");

class Tizi_Login extends Tizi_Controller {
	
	protected $_smarty_dir="login/";

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
		$remember=$this->input->post('remember',true);

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

		if(preg_suname($username))
		{
			if(stripos($redirect_url,'http://')!==false)
			{
				$this->session->set_userdata("sso_redirect",$redirect_url);
			}
			$this->smarty->assign('s_username',$username);
			$this->smarty->assign('s_password',$password);
			$this->smarty->assign('remember',$remember);
			$submit['slhtml']=$this->load_school_login();
			echo json_token($submit);
    		exit();
		}

		$user_id=$this->login_model->login($username,$password);

		if($user_id['errorcode']==Constant::LOGIN_SUCCESS)
		{
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
				$this->session->set_userdata("sso_redirect",$redirect_url);
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
					$create_info = $this->classes_student_create->id_create($create_pk);
					$this->session->set_userdata("sso_t", Constant::LOGIN_SSO_TYPE_TADD);
					$this->session->set_userdata("sso_id", $create_pk);
					$create_info["source"] == 1 && $this->session->set_userdata("sso_ro", Constant::REG_ORIGIN_SCHOOL_LOGIN);
					$submit["redirect"] = login_url("sso/student");
					$submit["errorcode"] = true;
				}
				else
				{
					//如果classes_student_create表没有则查询user_invite
					$this->load->model("login/user_invite_model");
					$user_invite = $this->user_invite_model->login($username, $password, "student_id", true);
					if (isset($user_invite) && $user_invite["user_type"] == Constant::USER_TYPE_STUDENT){
						$this->session->set_userdata("sso_t", Constant::LOGIN_SSO_TYPE_CARD);
						$this->session->set_userdata("sso_id", $user_invite["id"]);
						$submit["redirect"] = login_url("sso/student");
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
						$this->session->set_userdata("sso_t", Constant::LOGIN_SSO_TYPE_CARD);
						$this->session->set_userdata("sso_id", $user_invite["id"]);
						$submit["redirect"] = login_url("sso/teacher");
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
		$redirect=$this->input->get('redirect',true);
		if($redirect&&urldecode($redirect)) $redirect=urldecode($redirect);

        if($this->tizi_uid>0)
        {
        	$this->session_model->clear_session();
			$this->session_model->clear_cookie();
			$this->session_model->clear_current_dir_cookie();
        	if(strpos($redirect,'http://')===false && $site) $redirect=site_url('',$site);
			redirect($redirect);
		}
		else
		{
			redirect(site_url('',$site));
		}
	}

	public function check_login()
    {
    	$redirect=$this->input->get('redirect',true);
    	$reg_redirect=$this->input->get('href',true);
    	$reg_role=$this->input->get('reg_role',true);
    	$reg_url=$this->input->get('reg_url',true,true,'');
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
			$this->smarty->assign('reg_url',$reg_url);
			if(!$nohtml) $html=$this->smarty->fetch('[lib]common/tizi_login_form.html');
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

   	private function load_school_login(){
		$cookie_school_id = $this->input->cookie(Constant::COOKIE_SCHOOL_LOGIN);
		if(!$cookie_school_id) $cookie_school_id = 201365;
		if ($cookie_school_id > 0){
			$data = array();
			$school_id = $cookie_school_id;
			
			$this->load->model("class/classes_agents_model");
			$data["my"] = $this->classes_agents_model->get_by_school_id($school_id, "province_id,city_id,county_id,school_id");
			$data["province"] = $this->classes_agents_model->get_province();
			$data["province"] = $data["province"]["data"];
			$city = $this->classes_agents_model->get_city($data["my"]["province_id"]);
			foreach ($city as $key => $value){
				$data["city"][] = array("city_id" => $key, "city_name" => $value);
			}
			$data["school"] = $this->classes_agents_model->get_school($data["my"]["city_id"]);
			
			/*特区替换*/
			$this->load->helper("area");
			ismunicipality($data["my"]["province_id"]) && $data["my"]["city_id"] = $data["my"]["county_id"];
			
			$this->smarty->assign("school_login_info", $data);
			$this->smarty->assign("is_show_school", true);
		} else {
			$this->load->model("class/classes_agents_model");
			$data = $this->classes_agents_model->get_province();
			$this->smarty->assign("agents_province", $data["data"]);
			$this->smarty->assign("is_show_school", false);
		}
		$html=$this->smarty->fetch('[lib]common/tizi_login_school_form.html');
		return $html;
	}

}	
/* End of file login.php */
/* Location: ./application/controllers/login/login.php */
