<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once("tizi_controller.php");

class Tizi_Verify extends Tizi_Controller {

	protected $_smarty_dir="login/";

	function __construct()
    {
        parent::__construct();
	    $this->load->model("login/verify_model");
		$this->load->model("login/register_model");
    }

	function verify_email($code='')
	{
		$authcode=$this->input->get("code");
		if(!$authcode) $authcode=$code;
		if($authcode)
		{
	        $code_type=$this->verify_model->verify_authcode_email($authcode);
			$error='';
	        if($code_type['errorcode'])
	        {
	          	if($code_type['code_type']==Constant::CODE_TYPE_REGISTER)
				{
					$errorcode=$this->register_model->verify_email($code_type['user_id']);
					if($errorcode['errorcode'])
					{
						$this->smarty->display($this->_smarty_dir.'email_bind.html');
					}
					else
					{
						$error=$this->lang->line('error_verify_email_failed');
					}
				}
				else if($code_type['code_type']==Constant::CODE_TYPE_CHANGE_EMAIL)
	            {
					$errorcode=$this->register_model->update_email($code_type['user_id'],$code_type['email']);
	                if($errorcode['errorcode'])
	                {
	                   	$this->smarty->display($this->_smarty_dir.'email_bind.html');
	                }
	                else
	                {
						$error=$this->lang->line('error_verify_email_failed');
	                }
	            }
				else
				{
					$error=$this->lang->line('error_verify_email_failed');
				}
			}
	        else
	        {
				$error=$this->lang->line('error_verify_email_failed');
	        }
    	}
    	else
    	{
    		$error=$this->lang->line('error_verify_email_failed');
    	}

		if($error)
		{
			$this->session->set_flashdata('errormsg',$error);
			redirect($this->tizi_redirect);
		}
	}
	
	/*desc:send authcode with email*/
	/*input:get(email)*/
	/*output:email*/
	/*load:verify_model(generate_authcode_email)*/
	function send_email_code()
	{
		$email=$this->input->post("email",true);
		$code_type=$this->input->post("code_type",true);
	
		if($email)
        {
			$user_id=$this->register_model->get_user_id($email);
			if(empty($user_id)&&$this->tizi_uid)
			{
				$user_id=array('user_id'=>$this->tizi_uid,'user_type'=>$this->tizi_utype);
			}
			else 
			{
				$user_id=array('user_id'=>'','user_type'=>'');
			}
			$authcode = $this->verify_model->generate_authcode_email($email,$code_type,$user_id['user_id'],$user_id['user_type']);
			if($authcode['errorcode']&&$authcode['authcode'])
			{
				$errorcode=$this->verify_model->send_authcode_email($authcode['authcode'],$email,$code_type);
				if($errorcode['errorcode']) $errorcode['error']=$this->lang->line('success_send_auth_email');
				else $errorcode['error']=$this->lang->line('error_send_auth_email');
			}
			else
			{
				$error=$this->lang->line('error_authcode_email_limit');
				$errorcode=array('errorcode'=>false,'error'=>$error);
			}
		}
        else
        {
            $errorcode=array('errorcode'=>false,'error'=>$this->lang->line("error_invalid_email"));
        }
		echo json_token($errorcode);
		exit();
	} 

	/*desc:send authcode with phone*/
    /*input:get(phone)*/
    /*output:phone*/
    /*load:verify_model(generate_authcode_email)*/
	function send_phone_code()
	{
		$phone=$this->input->post("phone",true);
		$code_type=$this->input->post("code_type",true);	
		if($phone)
		{
			$checkphone=$this->register_model->check_phone($phone);
			if(!$checkphone['errorcode']&&$checkphone['user_id']==-127)
			{
				$errorcode['errorcode']=false;
				$errorcode['error']=$this->lang->line('default_error');	
			}
			else if($checkphone['errorcode']&&$code_type==Constant::CODE_TYPE_REGISTER)
			{
				$errorcode['errorcode']=false;
				$errorcode['error']=$this->lang->line('error_reg_exist_phone');	
			}
			else if($checkphone['errorcode']&&$checkphone['user_id']!=$this->tizi_uid&&$code_type==Constant::CODE_TYPE_CHANGE_PHONE)
			{
				$errorcode['errorcode']=false;
				$errorcode['error']=$this->lang->line('error_exist_phone');
			}
			else if(!$checkphone['errorcode']&&$code_type==Constant::CODE_TYPE_CHANGE_PASSWORD)
			{
				$errorcode['errorcode']=false;
				$errorcode['error']=$this->lang->line('error_reset_password_not_verify_phone');
			}
			else			
			{
				$authcode=$this->verify_model->generate_authcode_phone($phone,$code_type,$this->tizi_uid);
				if($authcode['errorcode']&&$authcode['authcode'])
				{
					$errorcode=$this->verify_model->send_authcode_phone($authcode['authcode'],$phone,$code_type);
            		if($errorcode['errorcode']) $errorcode['error']=$this->lang->line('success_send_auth_phone');
            		else $errorcode['error']=$this->lang->line('error_send_auth_phone');
            	}
				else
	        	{
	            	$error=$this->lang->line('error_authcode_phone_limit');
	            	$errorcode=array('errorcode'=>false,'error'=>$error);
	        	}	
        	}			
		}
		else
		{
			$errorcode=array('errorcode'=>false,'error'=>$this->lang->line("error_invalid_phone"));
		}
		echo json_token($errorcode);
		exit();
	}

}
/* End of file verify.php */
/* Location: ./application/controllers/login/verify.php */