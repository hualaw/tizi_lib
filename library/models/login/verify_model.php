<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Verify_Model extends LI_Model {

	private $_table="verify";
	private $_redis=false;
	private $_user_id=0;
	private $_session_id='';
	private $_remote_ip=0;
	
	function __construct()
	{
        parent::__construct();
		$this->load->model("redis/redis_model");
		$this->load->helper('string');

		$this->_session_id=$this->session->userdata('session_id');
		$this->_user_id=$this->session->userdata('user_id');

		if($this->redis_model->connect('verify'))
		{
			$this->_redis=true;
			$this->_remote_ip=ip2long(get_remote_ip());
		}
	}
	/*desc:generate authcode*/
	/*input:arg(email,code_type(1-register,2-change password,3-change email,4-change phone),user_type,user_id)*/
	/*output:return(authcode,errorcode(1-success,0-failed))*/
	function generate_authcode_email($email,$code_type=0,$user_id="",$user_type=Constant::USER_TYPE_TEACHER,$check=true)
	{
		$authcode="";
		if($check) $errorcode=$this->check_authcode_email($email,$code_type,$user_id);	
		else $errorcode=array('errorcode'=>false);
		if(!$errorcode['errorcode'])
        {	
            $authcode=random_string('unique');
            $data=$this->bind_verify($user_id,$email,"",1,$code_type,$authcode,$user_type);
		
			if($this->_redis)
            {
                $errorcode=$this->cache->save($authcode,json_encode($data),Constant::AUTHCODE_REDIS_EXPIRE_EMAIL);
				$this->save_check('email',$email,$authcode,Constant::SEND_REDIS_AUTHCODE_INTERVAL_EMAIL);
            }
			else
			{
				$this->db->insert($this->_table,$data);
           		$insert_id=$this->db->insert_id();
           		if($insert_id>0) $errorcode=true;
           		else $errorcode=false;
			}
			log_message('trace_tizi','170018:Gen email auth code',array('email'=>$email));
			if(!$errorcode) log_message('error_tizi','17018:Gen email auth code failed',array('email'=>$email));					
		}
		else
		{
			$errorcode=false;
		}
		return array('authcode'=>$authcode,'errorcode'=>$errorcode);
	}

	/*desc:generate authcode*/
    /*input:arg(phone,code_type(1-register,2-change password),user_type,user_id)*/
	/*output:return(authcode,errorcode(1-success,0-failed))*/
	function generate_authcode_phone($phone,$code_type=0,$user_id="",$user_type=Constant::USER_TYPE_TEACHER)
	{
		$authcode="";
		$errorcode=$this->check_authcode_phone($phone,$code_type);
        if(!$errorcode['errorcode'])
        {
			$authcode=random_string('nozero',6);
            $data=$this->bind_verify($user_id,"",$phone,2,$code_type,$authcode,$user_type);
			if($this->_redis)
            {
                $errorcode=$this->cache->save($authcode.'_'.sha1($phone),json_encode($data),Constant::AUTHCODE_REDIS_EXPIRE_PHONE);
				$this->save_check('phone',sha1($phone),$authcode,Constant::SEND_REDIS_AUTHCODE_INTERVAL_PHONE);
            }
			else
			{
				$this->db->insert($this->_table,$data);
				$insert_id=$this->db->insert_id();
				if($insert_id>0) $errorcode=true;
				else $errorcode=false;
			}
			log_message('trace_tizi','170019:Gen phone auth code',array('phone'=>$phone));
			if(!$errorcode) log_message('error_tizi','17019:Gen phone auth code failed',array('phone'=>$phone));
		}
        else
        {
            $errorcode=false;
        }
        return array('authcode'=>$authcode,'errorcode'=>$errorcode);
	}

	/*bind verify data*/
	function bind_verify($user_id,$email,$phone,$type,$code_type,$authcode,$user_type)
	{
		$this->load->helper('encrypt_helper');
		if($phone) 
		{
			$phone_mask=mask_phone($phone);
			//加密手机号码
			$phone=sha1($phone);
		}
		else
		{
			$phone_mask="";
		}
		$data=array(	
				'user_id'=>$user_id,
				'user_type'=>$user_type,
				'email'=>$email,
				'phone'=>$phone,//需要加密手机号码
				'phone_mask'=>$phone_mask,
				'type'=>$type,
				'code_type'=>$code_type,
				'authcode'=>$authcode,
				'has_verified'=>0,
				'generate_time'=>date("Y-m-d H:i:s"),
				'verified_time'=>null
		);		
		return $data;
	}

	private function save_check($type,$type_key,$authcode,$expire)
	{
		$this->cache->save($type_key,$authcode,$expire);
        $this->cache->save($type.'_'.$this->_session_id,$authcode,$expire);  
		$key=$type.'_'.$this->_remote_ip.'_'.date('Y_m_d_H_i');
        if($this->cache->get($key)) $this->cache->redis->incr($key);
        else $this->cache->save($key,1,120);
	}

	private function check_auth($type,$type_key)
	{
		$check=false;
		if($this->cache->get($type_key)) $check=true;
		else if($this->cache->get($type.'_'.$this->_session_id)) $check=true;
		else
		{	
			$key=$type.'_'.$this->_remote_ip.'_'.date('Y_m_d_H_i');
			$okey=$type.'_'.$this->_remote_ip.'_'.date('Y_m_d_H_i',strtotime("last minute"));
			$times=(int)$this->cache->get($key)+(int)$this->cache->get($okey);	
			if($times >= Constant::SEND_REDIS_AUTHCODE_TIMES) $check=true;
		}
		return $check;
	}

	/*desc:check authcode time*/
	/*input:arg(user_id,code_type,email)*/
	/*output:return(errorcode(1-success,0-failed))*/
	function check_authcode_email($email,$code_type,$user_id="")
	{
		if($email)
		{
			if($this->_redis)
            {
				$errorcode=$this->check_auth('email',$email);
            }
            else
            {
				$this->db->select("id");
				$this->db->from($this->_table);
				$this->db->where("email",$email);
				$this->db->where("code_type",$code_type);
				$this->db->where("has_verified",0);
				$this->db->where("generate_time >",date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." -".Constant::SEND_AUTHCODE_INTERVAL_EMAIL)));
				$query=$this->db->get();
				$total=$query->num_rows();
				if($total>0) $errorcode=true;
				else $errorcode=false;
			}
		}
		else
		{
			$errorcode=true;
		}
		return array("errorcode"=>$errorcode);	
	}
	/*desc:check authcode time*/
    /*input:arg(user_id,code_type,phone)*/
    /*output:return(errorcode(1-success,0-failed))*/
    function check_authcode_phone($phone,$code_type,$user_id="")
    {
		if($phone)
		{
			if($this->_redis)
			{
				$errorcode=$this->check_auth('phone',sha1($phone));
			}
			else
			{
				$this->db->select("id");
				$this->db->from($this->_table);
				$this->db->where("phone",sha1($phone));//需要通过服务获得加密后的电话号码
				$this->db->where("code_type",$code_type);
				$this->db->where("has_verified",0);
				$this->db->where("generate_time >",date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." - ".Constant::SEND_AUTHCODE_INTERVAL_PHONE)));
				$query=$this->db->get();
				$total=$query->num_rows();
				if($total>0) $errorcode=true;
				else $errorcode=false;
			}
		}
		else
		{
			$errorcode=true;
		}
		return array("errorcode"=>$errorcode);	
    }
	/*desc:send authcode*/
	/*input:arg(authcode,email,code_type)*/
	/*output:email,return(errorcode(1-success,0-failed))*/
	function send_authcode_email($authcode,$email,$code_type=0)
	{
		$this->load->library("mail");
		$this->load->model('login/register_model');
		$msg_head=$msg_end=$lang=$link=$stuid='';

		if($this->_user_id)
		{
			$user_info=$this->register_model->get_user_info($this->_user_id);
			if($user_info['errorcode'] && $user_info['user']->student_id)
			{		
				$stuid='您的学号是：'.$user_info['user']->student_id.'<br />';
			}
		}

		switch($code_type)
		{
			case Constant::CODE_TYPE_REGISTER: $link="verify";$lang="verify";break;
			case Constant::CODE_TYPE_CHANGE_PASSWORD: $link="forgot/reset";$lang="reset";break;
			case Constant::CODE_TYPE_CHANGE_EMAIL: $link="verify";$lang="update_email";break;
			case Constant::CODE_TYPE_REGISTER_VERIFY_EMAIL: $link="verify";$lang="reg_reverify";break;
			case Constant::CODE_TYPE_LOGIN_VERIFY_EMAIL: $link="verify";$lang="login_reverify";break;
			default:break;
		}

		if($lang && $link)
		{	
			//$authcode=site_url().$link."?code=".$authcode;
			$authcode=site_url().$link."/code/".$authcode;
			$subject=$this->lang->line('mail_subject_'.$lang);
			$msg_body=str_replace('{email}',$email,$this->lang->line('mail_body_'.$lang));
			
			$msg_body=str_replace('{stuid}',$stuid,$this->lang->line('mail_body_'.$lang));
			$msg_end=$this->lang->line('mail_end_'.$lang);			
		}
		$msg=$msg_body.'<br /><a href="'.$authcode.'">'.$authcode.'</a><br />'.$msg_end.'<br/>'.$this->lang->line('mail_disclaimer');

		$ret = Mail::send($email, $subject, $msg);
		if($ret['ret']==1)
		{
			$errorcode=true;
			log_message('info_tizi','170101:Email send success',$ret);	
		}
		else
		{
			$errorcode=false;
			log_message('error_tizi','17010:Email send failed',$ret);
		}
		return array('errorcode'=>$errorcode,'send_error'=>implode(',',$ret));
	}

	/*desc:send authcode*/
    /*input:arg(authcode,phone)*/
	/*output:phone,return(errorcode(1-success,0-failed))*/
	function send_authcode_phone($authcode,$phone,$code_type=0)
	{
		$this->load->library('sms');

		$msg_head=$msg_end=$lang='';
		switch($code_type)
		{
			case Constant::CODE_TYPE_REGISTER: $lang="verify";break;
			case Constant::CODE_TYPE_CHANGE_PASSWORD: $lang="reset";break;
			case Constant::CODE_TYPE_CHANGE_PHONE: $lang="update_phone";break;
			default:break;
		}

		if($lang)
		{
			$msg_head=str_replace('{phone}',substr($phone,-4),$this->lang->line('phone_body_'.$lang));
			$msg_end=$this->lang->line('phone_end_'.$lang);			
		}
		$msg=$msg_head.$authcode.$msg_end;

       	$this->sms->setPhoneNums($phone);
       	$this->sms->setContent($msg);
		$sms_error=$this->sms->send();	
		if($sms_error['error']=="Ok")
		{
			 $errorcode=true;
			 log_message('info_tizi','170111:Sms send success',$sms_error);	
		}
		else 
		{
			$errorcode=false;
			if($sms_error['status']==3) $sms_error['error']=$this->lang->line('error_sms_invalid_phone');
			else $sms_error['error']=$this->lang->line('error_sms_normal');
			log_message('error_tizi','17011:Sms send failed',$sms_error);
		}
		return array('errorcode'=>$errorcode,'error'=>$sms_error['error'],'status'=>$sms_error['status']);
	}
	/*desc:verify email*/
	/*input:arg(authcode)*/
	/*output:return(user_id,email,code_type,errorcode(1-success,0-failed))*/
	function verify_authcode_email($authcode)
	{
		$user_id=$email=$code_type=$user_type=0;
		if($this->_redis)
		{
			$data=$this->cache->get($authcode);	
			if(!empty($data))
			{
				$data=json_decode($data);
				$user_id=$data->user_id;
				$email=$data->email;
				$code_type=$data->code_type;
				$user_type=$data->user_type;	
				$this->cache->delete($authcode);
				$errorcode=true;
			}
			else
			{
				$errorcode=false;
				log_message('error_tizi','17052:email verify code failed',array('authcode'=>$authcode));
			}
		}
		else
		{
        	$this->db->select("id,user_id,email,code_type,user_type,generate_time");
        	$this->db->from($this->_table);
    	    $this->db->where("authcode",$authcode);
        	$this->db->where("has_verified",0);
			$this->db->where("type",Constant::VERIFY_TYPE_EMAIL);
        	$query=$this->db->get();
        	$total=$query->num_rows();
        	$gen_time=$query->row()->generate_time;
			if($total==1&&date("Y-m-d H:i:s",strtotime($gen_time." + ".Constant::AUTHCODE_EXPIRE_EMAIL))>date("Y-m-d H:i:s"))
        	{
            	$id=$query->row()->id;
            	$user_id=$query->row()->user_id;
            	$email=$query->row()->email;
				$code_type=$query->row()->code_type;
				$user_type=$query->row()->user_type;
            	$this->db->where('id',$id);
            	$this->db->update($this->_table,array('has_verified'=>1,'verified_time'=>date("Y-m-d H:i:s")));
            	if($this->db->affected_rows()==1) $errorcode=true;
				else $errorcode=false;
        	}
        	else
        	{
           		$errorcode=false;
        	}
		}
        return array('user_id'=>$user_id,'email'=>$email,'code_type'=>$code_type,'user_type'=>$user_type,'errorcode'=>$errorcode);
	}
	/*desc:verify phone*/
    /*input:arg(authcode,verify(1-verified,2-not verified))*/
    /*output:return(user_id,phone,code_type,errorcode(1-success,0-failed))*/
	function verify_authcode_phone($authcode,$phone,$verify=true)
	{
		$user_id=$code_type=0;
        if($this->_redis)
        {
            $data=$this->cache->get($authcode.'_'.sha1($phone));
            if(!empty($data))
            {
                $data=json_decode($data);
                $user_id=$data->user_id;
                $code_type=$data->code_type;
				if($verify) $this->cache->delete($authcode.'_'.sha1($phone));
                $errorcode=true;
            }
            else
            {
                $errorcode=false;
                log_message('error_tizi','17051:phone verify code failed',array('phone'=>$phone));
            }
        }
        else
		{
			$this->db->select("id,user_id,phone,code_type,generate_time");
			$this->db->from($this->_table);
			$this->db->where("authcode",$authcode);
			//加密手机号码	
			$e_phone=sha1($phone);	
		
			$this->db->where("phone",$e_phone);
			$this->db->where("has_verified",0);
			$this->db->where("type",Constant::VERIFY_TYPE_PHONE);
			$query=$this->db->get();	
			$total=$query->num_rows();
			if($total) $gen_time=$query->row()->generate_time;
			if($total==1&&date("Y-m-d H:i:s",strtotime($gen_time." + ".Constant::AUTHCODE_EXPIRE_PHONE))>date("Y-m-d H:i:s"))
			{
				$id=$query->row()->id;
				$user_id=$query->row()->user_id;
				//$phone=$query->row()->phone;//电话号码已加密
				$code_type=$query->row()->code_type;
				if($verify)
				{
					$this->db->where('id',$id);
					$this->db->update($this->_table,array('has_verified'=>1,'verified_time'=>date("Y-m-d H:i:s")));
					if($this->db->affected_rows()==1) $errorcode=true;
	                else $errorcode=false;
				}
				else
				{
					$errorcode=true;
				}
			}
			else
			{
				$errorcode=false;
			}
		}
		return array('user_id'=>$user_id,'phone'=>$phone,'code_type'=>$code_type,'errorcode'=>$errorcode);
	} 	
}
/* End of file verify_model.php */
/* Location: ./application/models/login/verify_model.php */
