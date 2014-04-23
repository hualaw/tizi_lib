<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login_Model extends LI_Model {
	
	private $_table="user";
	
	function __construct()
	{
       	parent::__construct();
		$this->load->database();
	}
	/*desc:login*/
	/*input:arg(username,password)*/
	/*output:return(user_id,errorcode(1-success,2-not verify,3-invalid username,4-error username or password),error)*/
	function login($username,$password)
	{
		$user_id=$user_type=0;
		$type=preg_utype($username);
		if($type==Constant::LOGIN_TYPE_EMAIL)
		{
			$this->db->where("email",$username);
		}
		else if($type==Constant::LOGIN_TYPE_PHONE)
		{
			//$this->db->where("phone",$username);//登陆时需要通过服务获取用户id
			$this->load->library("thrift");
			$uid = $this->thrift->get_uid($username);
			if ($uid == -127)
			{
				return array('user_id'=>null,'errorcode'=>Constant::LOGIN_INVALID_THRIFT,'error'=>'LOGIN_INVALID_THRIFT');
			}
			else if ($uid > 0)
			{
				$this->db->where("id", $uid);
			}
		}
		else if($type==Constant::LOGIN_TYPE_STUID)
		{
			$this->db->where("student_id",$username);
		}
		else if($type==Constant::LOGIN_TYPE_UNAME)
		{
			$this->db->where("uname",$username);
		}
		else
		{
			return array('user_id'=>null,'errorcode'=>Constant::LOGIN_INVALID_TYPE,'error'=>'LOGIN_INVALID_TYPE');
		}
		$this->db->select("id,verified,email,email_verified,phone,phone_verified,name,student_id,user_type,password,is_lock");
		$this->db->from($this->_table);
		$query=$this->db->get();	
		$total=$query->num_rows();
		if($total==1)
		{
			if(!$query->row()->is_lock)
			{
				if($query->row()->verified)
				{
					if(Constant::LOGIN_NEED_EMAIL_VERIFY && $type==Constant::LOGIN_TYPE_EMAIL&&$query->row()->email_verified==0)
					{
						$errorcode=Constant::LOGIN_NOT_VERIFY_EMAIL;
						$error='LOGIN_NOT_VERIFY_EMAIL';
					}
					else if($type==Constant::LOGIN_TYPE_PHONE&&$query->row()->phone_verified==0)
					{
						$errorcode=Constant::LOGIN_NOT_VERIFY_PHONE;
						$error='LOGIN_NOT_VERIFY_PHONE';
					}
					else
					{
						$password1=$query->row()->password;
	            		$this->load->helper('encrypt_helper');
						$password_salt=encrypt_password_salt($password1);
	            		$password=encrypt_password($password,$password_salt);
						if($password===$password1)
						{
							$user_id=$query->row()->id;
							$user_type=$query->row()->user_type;
							$errorcode=Constant::LOGIN_SUCCESS;
							$error="";
						}
						else
						{
							$errorcode=Constant::LOGIN_ERROR_USERNAME_OR_PASSWORD;
							$error='LOGIN_ERROR_USERNAME_OR_PASSWORD';
						}
					}		
				}
				else
				{
					$errorcode=Constant::LOGIN_NOT_VERIFY;
					$error='LOGIN_NOT_VERIFY';
				}
			}
			else
			{
				$errorcode=Constant::LOGIN_IS_BLOCK;
				$error='LOGIN_IS_BLOCK';
			}
		}
		else
		{
			$errorcode=Constant::LOGIN_INVALID_USERNAME;
			$error='LOGIN_INVALID_USERNAME';
		}
		return array('user_id'=>$user_id,'user_type'=>$user_type,'errorcode'=>$errorcode,'error'=>$error);		
	}
	
}
/* End of file login_model.php */
/* Location: ./application/models/login/login_model.php */
