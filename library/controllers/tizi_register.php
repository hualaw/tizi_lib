<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once("tizi_controller.php");

class Tizi_Register extends Tizi_Controller {

	protected $_smarty_dir="login/";

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
		$invite_code=$this->input->post("invite",true,true,'');

		if($invite_code)
		{
			$submit=$this->register_invite($email,$rname,$password,$password1,$mysubject,$redirect,$invite_code);
		}
		else
		{
			$submit=$this->register_teacher($email,$rname,$password,$password1,$mysubject,$redirect);
		}

		unset($submit['register']);
		echo json_token($submit);
    	exit();
    }

    public function student_submit()
    {
    	$email=$this->input->post("s_email",true,true);
		$password=$this->input->post("s_password",true);
		$password1=$this->input->post("s_repassword",true,false,$password);
		$rname=$this->input->post("s_name",true,true);
		$mygrade=$this->input->post("s_mygrade",true,false,Constant::DEFAULT_GRADE_ID);
		$redirect=$this->input->post("redirect",true);
		
		$submit=$this->register_student($email,$rname,$password,$password1,$mygrade,$redirect);

		unset($submit['register']);
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
		
		$submit=$this->register_parent($email,$rname,$password,$password1,$mygrade,$redirect);

		unset($submit['register']);
		echo json_token($submit);
    	exit();
    }

    public function class_submit()
    {
    	$euname=$this->input->post("s_username",true,true);
		$password=$this->input->post("s_password",true);
		$password1=$this->input->post("s_repassword",true,false,$password);
		$rname=$this->input->post("s_name",true,true);
		$mygrade=$this->input->post("s_mygrade",true,false,Constant::DEFAULT_GRADE_ID);
		$redirect=$this->input->post("redirect",true);
		if(strpos($redirect,'http://') === false) $redirect='';
		$class_code=$this->input->post("invite_class",true);
		$parent_phone=$this->input->post("s_pphone",true);
		
		$user_type=Constant::USER_TYPE_STUDENT;

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$class_check=$this->class_check($euname,$rname,$password,$password1,$class_code);

		if(!$class_check['errorcode'])
		{
			$submit['error']=$class_check['error'];
		}
		else if($parent_phone&&!preg_phone($parent_phone))
		{
			$check['error']=$this->lang->line('error_invalid_phone');
		}
		else
		{
			$reg_data=array('register_grade'=>$class_check['class_grade']?$class_check['class_grade']:$mygrade);
			
			if($class_check['utype']==Constant::LOGIN_TYPE_EMAIL)
			{
				$reg_data['register_origin']=Constant::REG_ORIGEN_CLASS_EMAIL;
				$register=$this->register_by_email($euname,$password,$rname,$user_type,$reg_data);
			}
			else
			{
				$reg_data['register_origin']=Constant::REG_ORIGEN_CLASS_UNAME;
				$register=$this->register_by_uname($euname,$password,$rname,$user_type,$reg_data);
			}

			if(!$register['errorcode'])
			{
				$submit['error']=$register['error'];
			}
			else
			{
				if($class_code)
				{
					//加入班级
					$this->load->model('class/classes_student');
					$this->classes_student->add($class_check['class_id'],$register['user_id'],time(),Classes_student::JOIN_METHOD_REGCLASS);
					//保存家长手机号码
					$this->load->model("user_data/student_data_model");
                	if($parent_phone) $this->student_data_model->update_parent_phone($register['user_id'],$parent_phone);
				}
				$submit['errorcode']=true;
				$submit['redirect']=$redirect?$redirect:redirect_url(Constant::USER_TYPE_STUDENT,'register');
			}
		}

		echo json_token($submit);
    	exit();
    }

    protected function register_teacher($email,$rname,$password,$password1,$mysubject,$redirect,$reg_data=array())
    {
		if(strpos($redirect,'http://') === false) $redirect='';

		$user_type=Constant::USER_TYPE_TEACHER;

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$reg_check=$this->register_check($email,$rname,$password,$password1);
		
		if(!$reg_check['errorcode'])
		{
			$submit['error']=$reg_check['error'];
		}
		else if(empty($mysubject))
		{
			$submit['error']=$this->lang->line('error_invalid_mysubject');
		}
		else if($mysubject&&!$this->question_subject_model->check_subject($mysubject,'binding'))
		{
			$submit['error']=$this->lang->line('error_invalid_mysubject');
		}
		else
		{
			$reg_data=array_merge(array('register_subject'=>$mysubject),$reg_data);
			$register=$this->register_by_email($email,$password,$rname,$user_type,$reg_data);
			if(!$register['errorcode'])
			{
				$submit['error']=$register['error'];
			}
			else
			{
				$submit['errorcode']=true;
				$submit['redirect']=$redirect?$redirect:redirect_url(Constant::USER_TYPE_TEACHER,'register');
				$submit['register']=$register;
			}
		}

		return $submit;
    }

    protected function register_invite($email,$rname,$password,$password1,$mysubject,$redirect,$invite_code)
    {
    	$invite_check=$this->invite_check($invite_code);

    	if($invite_check['errorcode'])
		{
			$submit=$this->register_teacher($email,$rname,$password,$password1,$mysubject,$redirect,array('register_invite'=>$invite_check['register_invite']));

			$this->load->model('user_data/invite_model');
	        $invite_info=array('user_id'=>$submit['register']['user_id'],'reg_invite'=>$invite_check['register_invite'],'name'=>$rname,'invite_way'=>$$invite_check['invite_type'],'reg_time'=>time());
			$this->invite_model->insert_succ_reg($invite_info);
		}

		return $submit;
    }

    protected function register_student($email,$rname,$password,$password1,$mygrade,$redirect,$reg_data=array())
    {
    	if(strpos($redirect,'http://') === false) $redirect='';
		
		$user_type=Constant::USER_TYPE_STUDENT;

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$reg_check=$this->register_check($email,$rname,$password,$password1);

		if(!$reg_check['errorcode'])
		{
			$submit['error']=$reg_check['error'];
		}
		else if(empty($mygrade))
		{
			$submit['error']=$this->lang->line('error_invalid_mygrade');
		}
		else if($mygrade&&!$this->student_data_model->check_grade($mygrade,true,true))
		{
			$submit['error']=$this->lang->line('error_invalid_mygrade');
		}
		else
		{
			$register=$this->register_by_email($email,$password,$rname,$user_type,array('register_grade'=>$mygrade));
			if(!$register['errorcode'])
			{
				$submit['error']=$register['error'];
			}
			else
			{
				$submit['errorcode']=true;
				$submit['redirect']=$redirect?$redirect:redirect_url(Constant::USER_TYPE_STUDENT,'register');
				$submit['register']=$register;
			}
		}

		return $submit;
    }

    protected function register_parent($email,$rname,$password,$password1,$mygrade,$redirect,$reg_data=array())
   	{
   		if(strpos($redirect,'http://') === false) $redirect='';

		$user_type=Constant::USER_TYPE_PARENT;

		$submit=array('errorcode'=>false,'error'=>'','redirect'=>'');

		$reg_check=$this->register_check($email,$rname,$password,$password1);
		
		if(!$reg_check['errorcode'])
		{
			$submit['error']=$reg_check['error'];
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
				$submit['register']=$register;
			}
		}

		return $submit;
   	}

    protected function register_by_email($email,$password,$rname,$user_type,$user_data=false,$auto_login=true)
   	{
   		$register=array('errorcode'=>false,'error'=>'');
		
		$user_id=$this->register_model->insert_register($email,$password,$rname,Constant::INSERT_REGISTER_EMAIL,$user_type,$user_data);
		if($user_id['errorcode'])
		{
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

   	protected function register_check($email,$rname,$password,$password1)
   	{
   		$check=array('errorcode'=>false,'error'=>'');

   		$check_email=$this->register_model->check_email($email);

		if(empty($email))
		{
			$check['error']=$this->lang->line('error_invalid_email');
		}
		else if($email&&!preg_email($email))
		{
			$check['error']=$this->lang->line('error_invalid_email');
		}
		else if($email&&$check_email['errorcode'])
		{
			$check['error']=$this->lang->line('error_reg_exist_email');
		}
		else if(empty($rname))
		{
			$check['error']=$this->lang->line('error_invalid_name');
		}
		else if(empty($password))
		{
			$check['error']=$this->lang->line('error_invalid_password');
		}
		else if($password!=$password1)
		{
			$check['error']=$this->lang->line('error_invalid_confirm_password');
		}
		else
		{
			$check['error']='';
			$check['errorcode']=true;
		}

		return $check;
   	}

   	protected function euname_check($euname,$rname,$password,$password1,$class_code)
   	{
   		$check=array('errorcode'=>false,'error'=>'');

   		$check['utype']=preg_utype($euname);
		if($check['utype']==Constant::LOGIN_TYPE_EMAIL)
		{
			$check_euname=$this->register_model->check_email($euname);
		}
		else if($check['utype']==Constant::LOGIN_TYPE_UNAME)
		{
			$check_euname=$this->register_model->check_uname($euname);
		}
		else
		{
			$euname=false;
		}

		if(empty($euname))
		{
			$check['error']=$this->lang->line('error_invalid_euname');
		}
		else if($euname&&$check_euname['errorcode'])
		{
			$check['error']=$this->lang->line('error_reg_exist_euname');
		}
		else if(empty($rname))
		{
			$check['error']=$this->lang->line('error_invalid_name');
		}
		else if(empty($password))
		{
			$check['error']=$this->lang->line('error_invalid_password');
		}
		else if($password!=$password1)
		{
			$check['error']=$this->lang->line('error_invalid_confirm_password');
		}
		else
		{
   			$check['error']='';
			$check['errorcode']=true;
		}

		return $check;
   	}

   	protected function invite_check($invite_code)
   	{
   		$invite=array('errorcode'=>false,'error'=>$this->lang->line('error_invalid_invite'));

		if($invite_code)
		{
			$invite_code=alpha_id(strtoupper($invite_code),true);
			$register_invite=substr($invite_code,2);
			$invite_type=substr($invite_code,0,2);
			$invite_user=$this->register_model->get_user_info($register_invite);

			if($register_invite&&$invite_type&&$invite_user['errorcode'])
			{
				$invite['errorcode']=true;
				$invite['register_invite']=$register_invite;
				$invite['invite_type']=$invite_type;
				$invite['error']='';
			}
		}

		return $invite;
   	}

   	protected function class_check($class_code)
   	{
		$class=array('errorcode'=>false,'error'=>$this->lang->line('error_invalid_class'));

		if($class_code)
   		{
			$this->load->model('class/classes');
	   		$class_id=alpha_id_num($class_code,true);
	   		//$class_id=alpha_id($class_code,true);

			$class_info=$this->classes->get($class_id,'class_grade,class_status');
			if(isset($class_info['class_grade'])&&$class_info['class_status']==0)
			{
				$class['errorcode']=true;
				$class['class_id']=$class_id;
				$class['class_grade']=$class_info['class_grade'];
				$class['error']='';
			}
		}

		return $class;
   	}

}
/* End of file register.php */
/* Location: ./application/controllers/login/register.php */
