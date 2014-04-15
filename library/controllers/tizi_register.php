<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tizi_Register extends MY_Controller {

	private $_smarty_dir="login/";

    function __construct()
    {
        parent::__construct();

		$this->load->model('login/register_model');
		$this->load->model('login/verify_model');
		$this->load->model("login/session_model");
		$this->load->model("user_data/student_data_model");
		$this->load->model('question/question_subject_model');
    }

    public function teacher_submit()
    {
    	$email=$this->input->post("t_email",true,true);
		$password=$this->input->post("t_password",true);
		$password1=$this->input->post("t_repassword",true,false,$password);
		$rname=$this->input->post("t_name",true,true);
		$mysubject=$this->input->post("t_mysubject",true,false,Constant::DEFAULT_SUBJECT_ID);
		$redirect=$this->input->post("redirect",true);
		if(strpos($redirect,'http://') === false) $redirect='';
		$invite_code=$this->input->post("invite",true);

		$user_type=Constant::USER_TYPE_TEACHER;

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$check_email=$this->register_model->check_email($email);
		
		if(empty($email))
		{
			$submit['error']=$this->lang->line('error_invalid_email');
		}
		else if($email&&!preg_email($email))
		{
			$submit['error']=$this->lang->line('error_invalid_email');
		}
		else if($email&&$check_email['errorcode'])
		{
			$submit['error']=$this->lang->line('error_reg_exist_email');
		}
		else if(empty($rname))
		{
			$submit['error']=$this->lang->line('error_invalid_name');
		}
		else if(empty($mysubject))
		{
			$submit['error']=$this->lang->line('error_invalid_mysubject');
		}
		else if($mysubject&&!$this->question_subject_model->check_subject($mysubject,'binding'))
		{
			$submit['error']=$this->lang->line('error_invalid_mysubject');
		}
		else if(empty($password))
		{
			$submit['error']=$this->lang->line('error_invalid_password');
		}
		else if($password!=$password1)
		{
			$submit['error']=$this->lang->line('error_invalid_confirm_password');
		}
		else
		{
			$register=$this->register_by_email($email,$password,$rname,$user_type,array('register_subject'=>$mysubject,'register_invite'=>$invite_code));
			if(!$register['errorcode'])
			{
				$submit['error']=$register['error'];
			}
			else
			{
				$submit['errorcode']=true;
				$submit['redirect']=$redirect?$redirect:redirect_url(Constant::USER_TYPE_TEACHER,'register');
			}
		}

		echo json_token($submit);
    	exit();
    }

    public function student_submit()
    {
    	$uname=$this->input->post("s_uname",true,true);
		$password=$this->input->post("s_password",true);
		$password1=$this->input->post("s_repassword",true,false,$password);
		$rname=$this->input->post("s_name",true,true);
		$mygrade=$this->input->post("s_mygrade",true,false,Constant::DEFAULT_GRADE_ID);
		$redirect=$this->input->post("redirect",true);
		if(strpos($redirect,'http://') === false) $redirect='';
		$invite_code=$this->input->post("invite",true);

		$user_type=Constant::USER_TYPE_STUDENT;

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$check_uname=$this->register_model->check_uname($uname);

		if(empty($uname))
		{
			$submit['error']=$this->lang->line('error_invalid_uname');
		}
		else if($uname&&!preg_uname($uname))
		{
			$submit['error']=$this->lang->line('error_invalid_uname');
		}
		else if($uname&&$check_uname['errorcode'])
		{
			$submit['error']=$this->lang->line('error_reg_exist_uname');
		}
		else if(empty($rname))
		{
			$submit['error']=$this->lang->line('error_invalid_name');
		}
		else if(empty($mygrade))
		{
			$submit['error']=$this->lang->line('error_invalid_mygrade');
		}
		else if($mygrade&&!$this->student_data_model->check_grade($mygrade,true,true))
		{
			$submit['error']=$this->lang->line('error_invalid_mygrade');
		}
		else if(empty($password))
		{
			$submit['error']=$this->lang->line('error_invalid_password');
		}
		else if($password!=$password1)
		{
			$submit['error']=$this->lang->line('error_invalid_confirm_password');
		}
		else
		{
			$register=$this->register_by_uname($uname,$password,$rname,$user_type,array('register_grade'=>$mygrade));
			if(!$register['errorcode'])
			{
				$submit['error']=$register['error'];
			}
			else
			{
				$submit['errorcode']=true;
				$submit['redirect']=$redirect?$redirect:redirect_url(Constant::USER_TYPE_STUDENT,'register');
			}
		}

		echo json_token($submit);
    	exit();
    }

    public function parent_submit()
    {
    	$email=$this->input->post("p_email",true,true);
		$password=$this->input->post("p_password",true);
		$password1=$this->input->post("p_repassword",true,false,$password);
		$rname=$this->input->post("p_name",true,true);
		$redirect=$this->input->post("redirect",true);
		if(strpos($redirect,'http://') === false) $redirect='';
		$invite_code=$this->input->post("invite",true);

		$user_type=Constant::USER_TYPE_PARENT;

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$check_email=$this->register_model->check_email($email);
		
		if(empty($email))
		{
			$submit['error']=$this->lang->line('error_invalid_email');
		}
		else if($email&&!preg_email($email))
		{
			$submit['error']=$this->lang->line('error_invalid_email');
		}
		else if($email&&$check_email['errorcode'])
		{
			$submit['error']=$this->lang->line('error_reg_exist_email');
		}
		else if(empty($rname))
		{
			$submit['error']=$this->lang->line('error_invalid_name');
		}
		else if(empty($password))
		{
			$submit['error']=$this->lang->line('error_invalid_password');
		}
		else
		{
			$register=$this->register_by_email($email,$password,$rname,$user_type);
			if(!$register['errorcode'])
			{
				$submit['error']=$register['error'];
			}
			else
			{
				$submit['errorcode']=true;
				$submit['redirect']=$redirect?$redirect:redirect_url(Constant::USER_TYPE_PARENT,'register');
			}
		}

		echo json_token($submit);
    	exit();
    }

    protected function register_by_email($email,$password,$rname,$user_type,$user_data=false,$auto_login=true)
   	{
   		$register=array('errorcode'=>false,'error'=>'');
		
		$user_id=$this->register_model->insert_register($email,$password,$rname,Constant::INSERT_REGISTER_EMAIL,$user_type,$user_data);
		if($user_id['errorcode'])
		{							
			$authcode=$this->verify_model->generate_authcode_email($email,Constant::CODE_TYPE_REGISTER,$user_id['user_id'],$user_type,false);
			if($authcode['errorcode'])
			{
				$this->verify_model->send_authcode_email($authcode['authcode'],$email,Constant::CODE_TYPE_REGISTER);
			
				if(!Constant::LOGIN_NEED_EMAIL_VERIFY)
				{
					//login
					if($auto_login)
					{
						$this->session_model->generate_session($user_id['user_id']);
						$this->session_model->generate_cookie($email,$user_id['user_id']);
						$this->session_model->clear_mscookie();
					}
				}

				$register['user_id']=$user_id['user_id'];
				$register['errorcode']=true;
			}
			else
			{
				$register['error']=$this->lang->line('error_send_authcode_email');	
			}
		}
		else
		{
			$register['error']=$this->lang->line('error_reg_insert');
		}

		return $register;
   	}

   	protected function register_by_uname($uname,$password,$rname,$user_type,$user_data=false,$auto_login=true)
   	{
   		$register=array('errorcode'=>false,'error'=>'');
		
		$user_id=$this->register_model->insert_register($uname,$password,$rname,Constant::INSERT_REGISTER_UNAME,$user_type,$user_data);
		if($user_id['errorcode'])
		{
			//login
			if($auto_login)
			{	
				$this->session_model->generate_session($user_id['user_id']);
				$this->session_model->generate_cookie($uname,$user_id['user_id']);
				$this->session_model->clear_mscookie();
			}
			$register['user_id']=$user_id['user_id'];
			$register['errorcode']=true;
		}
		else
		{
			$register['error']=$this->lang->line('error_reg_insert');
		}

		return $register;
   	}

}
/* End of file register.php */
/* Location: ./application/controllers/login/register.php */
