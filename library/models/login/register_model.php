<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Register_Model extends LI_Model {
	
	private $_table="user";
		
	function __construct()
	{
        	parent::__construct();
		$this->load->database();
	}
	/*desc:insert new register to db*/
	/*input:arg(username,password,name,type(1-email,2-phone),user_type(1-admin,2-student,3-teacher,4-parent))*/
	/*output:return(userid,errorcode(1-success,0-duplicate))*/
	function insert_register($username,$password,$name,$type,$user_type=Constant::USER_TYPE_TEACHER,$user_data=false,$send_email=true)
	{
		$username=strip_tags(trim($username));
    	if(!$username) return array('errorcode'=>false,'user_id'=>0,'student_id'=>0);

		$data=$this->bind_register($username,$password,$name,$type,$user_type,$user_data);
		$this->db->insert($this->_table,$data);
		$insert_id = $this->db->insert_id();
		if($insert_id > 0)
		{
			$errorcode=true;	
			if($send_email&&$data['email'])
			{
				$this->load->model('login/verify_model');
                $authcode=$this->verify_model->generate_authcode_email($data['email'],Constant::CODE_TYPE_REGISTER,$insert_id,$data['user_type'],false);
                if($authcode['errorcode']) $this->verify_model->send_authcode_email($authcode['authcode'],$data['email'],Constant::CODE_TYPE_REGISTER);
			}	
			/* thrift insert start */
			if ($type == Constant::INSERT_REGISTER_PHONE)
			{
				$this->load->library("thrift");
				$response = $this->thrift->add_phone($insert_id, $username);
				if ($response != 1)
				{
					//$this->db->delete("user", array("id" => $insert_id));
					$errorcode = false;
					//$this->db->where('id',$insert_id);
					//$this->db->update($this->_table,array('phone_verified'=>0,'phone_mask'=>''));
					log_message('error_tizi','200011:Register insert error',array('uid'=>$insert_id,'username'=>$username,'register_type'=>$type,'user_type'=>$user_type));
				}
			}
			/* thrift insert over */
		} 
		else 
		{
			$errorcode=false;
		}
		if($errorcode) log_message('info_tizi','20001:Register success',array('uid'=>$insert_id,'username'=>$username,'register_type'=>$type,'user_type'=>$user_type));
		return array('user_id'=>$insert_id,'student_id'=>$data['student_id'],'errorcode'=>$errorcode);
	}
	/*bind register data*/
	function bind_register($username,$password,$name,$type,$user_type,$user_data=false)
	{
		$this->load->helper('string');
        $password_salt=random_string('alnum','6');
        $this->load->helper('encrypt_helper');
        if($password) $password=encrypt_password($password,$password_salt);
        else $password=NULL;
		
		$email=$phone=$uname=$student_id=$phone_mask=$qq=null;
		$email_verified=$phone_verified=0;

		$verified=1;
		if(Constant::LOGIN_NEED_EMAIL_VERIFY) $verified=0;

		$register_uid=$this->input->cookie('uid');

        switch($type)
        {
            case Constant::INSERT_REGISTER_EMAIL:	$email=$username;
            										$email_verified=0;
            										$origin=Constant::REG_ORIGIN_WEB_EMAIL;
            										break;

            case Constant::INSERT_REGISTER_PHONE: 	$phone=$username;
            										$phone_verified=1;
													$phone_mask=mask_phone($username);
													$origin=Constant::REG_ORIGIN_WEB_PHONE;
													break;
			case Constant::INSERT_REGISTER_STUID:	$student_id=$username!=""?$username:$this->get_student_id();
													$origin=Constant::REG_ORIGIN_WEB_STUID;
													break;
			case Constant::INSERT_REGISTER_UNAME:	$uname=$username;
													$origin=Constant::REG_ORIGIN_WEB_UNAME;
													break;
            default:break;
        }
        $data=array(
        		'verified'=>$verified,
				'email'=>$email,
				'email_verified'=>$email_verified,
				'phone_verified'=>$phone_verified,
				'phone_mask'=>$phone_mask,
				'uname'=>$uname,
				'password'=>$password,
				'name'=>$name,
				'student_id'=>$student_id,
				//'qq'=>$qq,
				'user_type'=>$user_type,
				'register_time'=>date("Y-m-d H:i:s"),
				'register_ip'=>ip2long(get_remote_ip()),
				'register_uid'=>$register_uid?$register_uid:NULL,
				'register_origin'=>$origin
		);
		if (is_array($user_data))
		{
			$data = array_merge($data, $user_data);
		}
		return $data;
	}
	/*get student id*/
	public function get_student_id()
	{
		/**
		$this->load->helper('string');
		return "2".random_string('numeric',7);	
		*/
		$this->load->model('class/classes_student_create');
		return $this->classes_student_create->get_stuid();
	}
	/*desc:email verified*/
    /*input:arg(user_id)*/
    /*output:return(errorcode(1-success,0-duplicate))*/
    function verify_email($user_id)
    {
		$this->db->where('id',$user_id);
        $this->db->update($this->_table,array('email_verified'=>1,'verified'=>1));
        if($this->db->affected_rows()==1){
			$errorcode=true;
			$this->load->library("credit");
			$this->credit->exec($user_id, "certificate_email");
		} else {
			$errorcode=false;
		}
		return array('errorcode'=>$errorcode);
    }

	/*desc:phone verified*/
    /*input:arg(user_id)*/
    /*output:return(errorcode(1-success,0-duplicate))*/
    function verify_phone($user_id)
    {
        $this->db->where('id',$user_id);
        $this->db->update($this->_table,array('phone_verified'=>1));
        if($this->db->affected_rows()==1){
			$errorcode=true;
			$this->load->library("credit");
			$this->credit->exec($user_id, "certificate_phone");
		} else {
			$errorcode=false;
		}
        return array('errorcode'=>$errorcode);
    }

	/*desc:check dupicate register*/
	/*input:arg(email,verify)*/
	/*output:return(errorcode(1-success,0-duplicate))*/
	function check_email($email,$verify=false)
	{
		$email=strip_tags(trim($email));
		if(empty($email)) return array('errorcode'=>false,'verified'=>0,'email_verified'=>0,'user_id'=>0);
		$this->db->where('email',$email);
		if($verify) $this->db->where('email_verified',1);
		$query=$this->db->get($this->_table);
		$total=$query->num_rows();
		if($total)
		{
			$errorcode=true;
			$user_id=$query->row()->id;
			$email_verified=$query->row()->email_verified;
			$verified=$query->row()->verified;
		}
		else
		{
			$errorcode=false;
			$user_id=0;
			$email_verified=0;
			$verified=0;
		}
		return array('verified'=>$verified,'email_verified'=>$email_verified,'user_id'=>$user_id,'errorcode'=>$errorcode);
	}

	/*desc:check dupicate register*/
    /*input:arg(phone)*/
    /*output:return(errorcode(1-exist,0-not exist))*/
    function check_phone($phone)
    {
    	$phone=strip_tags(trim($phone));
    	if(empty($phone)) return array('errorcode'=>false,'user_id'=>0);
    	$this->load->library("thrift");
    	$phone = $this->thrift->get_uid($phone);
    	if ($phone > 0)
    	{
    		$errorcode = true;
    	}
    	else 
    	{
    		$errorcode = false;
    	}
    	return array('errorcode'=>$errorcode,'user_id'=>$phone);
    }

    /*desc:check dupicate register*/
	/*input:arg(uname)*/
	/*output:return(errorcode(1-success,0-duplicate))*/
	function check_uname($uname)
	{
		$uname=strip_tags(trim($uname));
		if(empty($uname)) return array('errorcode'=>false);
		$this->db->where('uname',$uname);
		$query=$this->db->get($this->_table);
		$total=$query->num_rows();
		if($total)
		{
			$errorcode=true;
		}
		else
		{
			$this->load->model("login/user_invite_model");
			if (true === $this->user_invite_model->check($uname))
			{
				$errorcode=true;
			}
			else 
			{
				$errorcode=false;
			}
		}
		return array('errorcode'=>$errorcode);
	}

	/*desc:check dupicate register*/
	/*input:arg(stuid)*/
	/*output:return(errorcode(1-success,0-duplicate))*/
	function check_stuid($stuid)
	{
		$stuid=strip_tags(trim($stuid));
		if(empty($stuid)) return array('errorcode'=>false);
		$this->db->where('student_id',$stuid);
		$query=$this->db->get($this->_table);
		$total=$query->num_rows();
		if($total)
		{
			$errorcode=true;
		}
		else
		{
			$errorcode=false;
		}
		return array('errorcode'=>$errorcode);
	}

	/*desc:get verify password salt*/
	function get_password_salt($user_id,$namespace='password')
	{
		$this->db->select('password');
		$this->db->where('id',$user_id);
		$password=$this->db->get($this->_table)->row()->{$namespace};
		if($password)
		{
			$this->load->helper('encrypt_helper');
			$salt=encrypt_password_salt($password);
		}
		else
		{
			$salt=false;
		}
		return $salt;
	}

	/*desc:verify password*/
    /*input:arg(user_id,password)*/
    /*output:return(errorcode(1-success,0-duplicate))*/
	function verify_password($user_id,$password)
	{
		$password_salt=$this->get_password_salt($user_id);
		$this->load->helper('encrypt_helper');
		$password=encrypt_password($password,$password_salt);

		$this->db->where('id',$user_id);
        $query=$this->db->get($this->_table);
		if($query->num_rows()==1)
		{	
			$password1=$query->row()->password;
			if($password===$password1) $errorcode=true;
			else $errorcode=false;
		}
        else
		{
			$errorcode=false;
		}
        return array('errorcode'=>$errorcode);	
	}
	
	function compare_password($pwd_origin, $pwd_encode){
		$this->load->helper("encrypt_helper");
		$salt=encrypt_password_salt($pwd_encode);
		$password=encrypt_password($pwd_origin,$salt);
		return $password === $pwd_encode;
	}
	
	/*desc:update email*/
	/*input:arg(user_id,email,verified)*/
	/*output:return(errorcode(1-success,0-duplicate)*/
	function update_email($user_id,$email,$verified=1)
	{
		$email=strip_tags(trim($email));
    	if(!$email) return array('errorcode'=>false);

		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->email==$email&&$user['user']->email_verified==$verified)
		{
			$update=true;
		}
		else
		{
			$this->db->where("id",$user_id);
			$this->db->update($this->_table,array("email"=>$email,"email_verified"=>$verified));
			if($this->db->affected_rows()==1){
				$errorcode=true;
				if ($verified == 1){
					$this->load->library("credit");
					$this->credit->exec($user_id, "certificate_email");
				}
			} else {
				$errorcode=false;
			}
			if(!$errorcode) log_message('error_tizi','17012:Email update failed',array('uid'=>$user_id,'email'=>$email));
		}
		return array('errorcode'=>$errorcode);
	}
	
	function update_email_verified($user_id,$verified=1)
	{
		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->email_verified==$verified)
		{
			$update=true;
		}
		else
		{
			$this->db->where("id",$user_id);
	    	$this->db->update($this->_table,array("email_verified"=>$verified));
	    	$update=$this->db->affected_rows();
	    	if($update==1 && $verified == 1){
				$this->load->library("credit");
				$this->credit->exec($user_id, "certificate_email");
			} else {
				$errorcode=false;
			}
	    }
	    return $update;
	}

	/*desc:update iphone*/
    /*input:arg(user_id,iphone,verified)*/
    /*output:return(errorcode(1-success,0-duplicate)*/
	function update_phone($user_id,$phone,$verified=1)
	{
		$phone=strip_tags(trim($phone));
    	if(!$phone) return array('errorcode'=>false);
		
		/* 更新thrift start */
		$this->load->library("thrift");
		$response=0;
		$this->db->select('phone_verified');
		$this->db->where('id',$user_id);
		$query=$this->db->get($this->_table);
		if($query->num_rows()==1)
		{
			if($query->row()->phone_verified) $response = $this->thrift->change_phone($user_id, $phone);
			else $response = $this->thrift->add_phone($user_id, $phone);
			/* 更新thrift over **/
		}

		$phone_mask='';
		if ($response == 1)
		{
			$this->load->helper('encrypt_helper');
			$phone_mask=mask_phone($phone);
			$this->db->where("id",$user_id);
	        $this->db->update($this->_table,array("phone_verified"=>$verified,"phone_mask"=>$phone_mask));
	        if($this->db->affected_rows()==1){
				$errorcode=true;
				if ($verified == 1){
					$this->load->library("credit");
					$this->credit->exec($user_id, "certificate_phone");
				}
			} else {
				$errorcode=false;
			}
		} 
		else 
		{
			$errorcode = false;
			log_message('error_tizi','17013:Phone update failed',array('uid'=>$user_id,'phone'=>$phone_mask));
		}
        return array('errorcode'=>$errorcode);	
	}

	function update_phone_verified($user_id,$verified=1)
	{
		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->phone_verified==$verified)
		{
			$update=true;
		}
		else
		{
			$this->db->where("id",$user_id);
	    	$this->db->update($this->_table,array("phone_verified"=>$verified));
	    	$update=$this->db->affected_rows();
	    	if($update==1 && $verified == 1){
				$this->load->library("credit");
				$this->credit->exec($user_id, "certificate_phone");
			} else {
				$errorcode=false;
			}
	    }
	    return $update;
	}

	/*desc:encrypt password*/
	function encrypt_password($password,$salt=false)
	{
		if(!$salt)
		{
			$this->load->helper('string');
       		$password_salt=random_string('alnum','6');
		}
		else
		{
			$password_salt=$salt;
		}
        $this->load->helper('encrypt_helper');
        $password=encrypt_password($password,$password_salt);		
		return $password;
	}	

	/*desc:update password*/
    /*input:arg(user_id,password)*/
    /*output:return(errorcode(1-success,0-failed)*/ 
	function update_password($user_id,$password)
	{
		$password=$this->encrypt_password($password);

		$this->db->where("id",$user_id);
        $this->db->update($this->_table,array("password"=>$password));
        if($this->db->affected_rows()==1)
		{
			$errorcode=true;
        }
		else 
		{
			$errorcode=false;
			$this->db->where('id',$user_id);
			$this->db->where('password',$password);
			$query=$this->db->get($this->_table);
			if($query->num_rows()==1) $errorcode=true;
        }
		if(!$errorcode) log_message('error_tizi','17014:Password update failed',array('uid'=>$user_id));
		return array('errorcode'=>$errorcode);
	}

	/*desc:update subject*/
    /*input:arg(user_id,subject,force)*/
    /*output:return(errorcode(1-success,0-failed)*/
    function update_subject_type($user_id,$subject,$force=false)
    {
		$this->load->model('question/question_subject_model');
		$check_subject_type=$this->question_subject_model->check_subject_type($subject);
		if(!$check_subject_type) return array('errorcode'=>false);

		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->register_subject==$subject)
		{
			$errorcode=true;
		}
		else
		{
			if($subject) $this->db->where("id",$user_id);
			if(!$force) $this->db->where('register_subject',null);
	        $this->db->update($this->_table,array("register_subject"=>$subject));
	        if($this->db->affected_rows()==1) $errorcode=true;
	        else $errorcode=false;
			if(!$errorcode) log_message('error_tizi','17015:Subject update failed',array('uid'=>$user_id,'subject'=>$subject));
        }
        return array('errorcode'=>$errorcode);
    }

    /*desc:update mysubject*/
    /*input:arg(user_id,mysubject,force)*/
    /*output:return(errorcode(1-success,0-failed)*/
    function update_mysubject($user_id,$mysubject,$force=true)
    {
		$this->load->model('question/question_subject_model');
		$check_subject=$this->question_subject_model->check_subject($mysubject);
		if(!$check_subject) return array('errorcode'=>false);

		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->register_subject==$mysubject)
		{
			$errorcode=true;
		}
		else
		{
			if($mysubject) $this->db->where("id",$user_id);
			if(!$force) $this->db->where('register_subject',null);
	        $this->db->update($this->_table,array("register_subject"=>$mysubject));
	        if($this->db->affected_rows()==1) 
	        {
	        	$errorcode=true;
	        	$this->session->set_userdata('register_subject',$mysubject);
	        }
	        else 
	        {
	        	$errorcode=false;
	        }
			if(!$errorcode) log_message('error_tizi','17015:MySubject update failed',array('uid'=>$user_id,'mysubject'=>$mysubject));
        }
        return array('errorcode'=>$errorcode);
    }

    /*desc:update mygrade*/
    /*input:arg(user_id,mygrade,force)*/
    /*output:return(errorcode(1-success,0-failed)*/
    function update_mygrade($user_id,$mygrade,$force=true)
    {
		$this->load->model('user_data/student_data_model');
		$check_grade=$this->student_data_model->check_grade($mygrade,true,true);
		if(!$check_grade) return array('errorcode'=>false);

		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->register_grade==$mygrade)
		{
			$errorcode=true;
		}
		else
		{
			if($mygrade) $this->db->where("id",$user_id);
			if(!$force) $this->db->where('register_grade',null);
	        $this->db->update($this->_table,array("register_grade"=>$mygrade));
	        if($this->db->affected_rows()==1) 
	        {
	        	$errorcode=true;
	        	$this->session->set_userdata('register_grade',$mygrade);
	        }
	        else 
	        {
	        	$errorcode=false;
	        }
			if(!$errorcode) log_message('error_tizi','17015:MyGrade update failed',array('uid'=>$user_id,'mygrade'=>$mygrade));
		}
		return array('errorcode'=>$errorcode);
    }

    /*desc:update uname*/
    /*input:arg(user_id,uname)*/
    /*output:return(errorcode(1-success,0-failed)*/
    function update_uname($user_id,$uname,$force=false)
    {
    	$uname=strip_tags(trim($uname));
    	if(!$uname) return array('errorcode'=>false);

		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->uname==$uname)
		{
			$errorcode=true;
		}
		else
		{
	 	    $this->db->where("id",$user_id);
	 	    if(!$force) $this->db->where("(uname is NULL or uname = '')");
	        $this->db->update($this->_table,array("uname"=>$uname));
	        if($this->db->affected_rows()==1) 
	        {
	        	$errorcode=true;
	        	$this->session->set_userdata('uname',$uname);
	        }	
	        else
	        {
	        	$errorcode=false;
		        log_message('error_tizi','170161:UName update failed',array('uid'=>$user_id,'uname'=>$uname));
			}
        }
        return array('errorcode'=>$errorcode);
    }

	/*desc:update name*/
    /*input:arg(user_id,name)*/
    /*output:return(errorcode(1-success,0-failed)*/
    function update_name($user_id,$name)
    {
    	$name=strip_tags(trim($name));
    	if(!$name) return array('errorcode'=>false);

		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->name==$name)
		{
			$errorcode=true;
		}
		else
		{
	 	    $this->db->where("id",$user_id);
	        $this->db->update($this->_table,array("name"=>$name));
	        if($this->db->affected_rows()==1) 
	        {
	        	$errorcode=true;
	        	$this->session->set_userdata('urname',$name);
	        }	
	        else
	        {
	        	$errorcode=false;
		        log_message('error_tizi','17016:Name update failed',array('uid'=>$user_id,'name'=>$name));
			}
        }
        return array('errorcode'=>$errorcode);
    }

    /*desc:update avatar*/
    /*input:arg(user_id,avatar)*/
    /*output:return(errorcode(1-success,0-failed)*/
    function update_avatar($user_id,$avatar=1)
    {
		$user=$this->get_user_info($user_id);
		if($user['errorcode']&&$user['user']->avatar==$avatar)
		{
			$errorcode=true;
		}
		else
		{
	 	    $this->db->where("id",$user_id);
	        $this->db->update($this->_table,array("avatar"=>$avatar));
	        if($this->db->affected_rows()==1) 
	        {
	        	$errorcode=true;
	        	$this->session->set_userdata('avatar',$avatar);
	        }	
	        else
	        {
	        	$errorcode=false;
		        log_message('error_tizi','170163:Avatar update failed',array('uid'=>$user_id));
			}
        }
        return array('errorcode'=>$errorcode);
    }

	/*desc:get user_id*/
	/*input:arg(email,phone)*/
	/*output:return(user_id,errorcode(1-success,0-invalid user))*/
	function get_user_id($search,$utype = Constant::LOGIN_TYPE_EMAIL, $fields = "id,user_type")
	{
		$user_id=$user_type=0;
		if($utype == Constant::LOGIN_TYPE_PHONE)
		{
			$this->load->library("thrift");
        	$uid=$this->thrift->get_uid($search);
			if($uid>0) $this->db->where("id",$uid);	
			else return array('user_id'=>$user_id,'errorcode'=>false);
		}
		else if($utype == Constant::LOGIN_TYPE_STUID)
		{
			$this->db->where("student_id", $search);
		}
		else if($utype == Constant::LOGIN_TYPE_UNAME)
		{
			$this->db->where("uname", $search);
		}
		else
		{
			$this->db->where("email",$search);
		}
		$this->db->select($fields);
		$this->db->from($this->_table);
		$query=$this->db->get();
		$total=$query->num_rows();
		if($total==1)
		{
			$data = $query->row_array();
			$data["user_id"] = isset($data["id"]) ? $data["id"] : 0;
			$data["errorcode"] = true;
		}
		else
		{
			$data["errorcode"] = false;
		}
		return $data;
	}

	/*get user information*/
	/*input:arg(user_id)*/
	/*output:return(user,errorcode(1-success,0-invalid user))*/
	function get_user_info($user_id,$utype=0)
	{
		if(!$user_id) return array('user'=>array(),'errorcode'=>false);

		switch ($utype) {
			case Constant::LOGIN_TYPE_STUID:$this->db->where('student_id',$user_id);break;
			case Constant::LOGIN_TYPE_UNAME:$this->db->where('uname',$user_id);break;
			case 0: $this->db->where('id',$user_id);		
			default:break;
		}		
		$query=$this->db->get($this->_table);
		$total=$query->num_rows();
		$user=array();
		if($total==1)
        {
            $user=$query->row();
			$user->phone=$user->phone_mask;
            $errorcode=true;
        }
        else
        {
            $errorcode=false;
        }
        return array('user'=>$user,'errorcode'=>$errorcode);		
	}

	function get_phone($user_id)
    {
    	if(empty($user_id)) return array('errorcode'=>false,'phone'=>'');
    	$this->load->library("thrift");
    	$phone = $this->thrift->get_phone($user_id);
    	if ($phone > 0)
    	{
    		$errorcode = true;
    	}
    	else 
    	{
    		$errorcode = false;
    	}
    	return array('errorcode'=>$errorcode,'phone'=>$phone);
    }

	public function my_subject($user_id,$cookie_name='')
	{
		$my_subject=$this->my_favorate_subject($cookie_name);
		if($my_subject)
		{
			return $my_subject;
		}
		else
		{
			$this->db->select('register_subject');
			$this->db->where('id',$user_id);
			$query=$this->db->get($this->_table);	
			$total=$query->num_rows();
			if($total==1)	
			{
				return $query->row()->register_subject;
			}
			else
			{
				return false;
			}
		}
	}

	public function my_favorate_subject($cookie_name='')
	{
		switch ($cookie_name) 
		{
			case 'paper':$cookie=Constant::COOKIE_TZMYSUBJECT_PAPER;break;
			case 'doc':$cookie=Constant::COOKIE_TZMYSUBJECT_DOC;break;
			case 'homework':$cookie=Constant::COOKIE_TZMYSUBJECT_HOMEWORK;break;
			default:$cookie=Constant::COOKIE_TZMYSUBJECT;break;
		}
		$my_subject=$this->input->cookie($cookie);
		return $my_subject;
	}

	public function set_favorate_subject($my_subject,$cookie_name='')
	{
		switch ($cookie_name) 
		{
			case 'paper':$cookie=Constant::COOKIE_TZMYSUBJECT_PAPER;break;
			case 'doc':$cookie=Constant::COOKIE_TZMYSUBJECT_DOC;break;
			case 'homework':$cookie=Constant::COOKIE_TZMYSUBJECT_HOMEWORK;break;
			default:$cookie=Constant::COOKIE_TZMYSUBJECT;break;
		}
		$old_my_subject=$this->input->cookie($cookie);
		if($my_subject != $old_my_subject)
		{
			$this->input->set_cookie($cookie,$my_subject,Constant::COOKIE_MYSUBJECT_EXPIRE_TIME);
		}
		return;
	}

	public function my_current_cloud_dir()
	{
		return $this->input->cookie(Constant::COOKIE_CURRENT_CLOUD_DIR);
	}

	public function set_current_cloud_dir($dir)
	{
		$cookie = Constant::COOKIE_CURRENT_CLOUD_DIR;
		$old_my_dir=$this->input->cookie($cookie);
		if($dir != $old_my_dir)
		{
			$this->input->set_cookie($cookie,$dir,Constant::COOKIE_MYDIR_EXPIRE_TIME);
		}
		return;
	}


	/**
	 * get_user bind subject_id and realname
	 * @param integer $user_id
	 * @return Ambigous <NULL, number>
	 */
	public function get_san($user_id){
		$rs = $this->db->query("select name,register_subject from `user` where id=?", array($user_id))->result_array();
		$san = array(
			'name' => '',
			'register_subject' => ''
		);
		if (isset($rs[0]) && $rs[0]['name'] !== null && $rs[0]['name'] !== ''){
			$san['name'] = $rs[0]['name'];
		}
		if (isset($rs[0]) && $rs[0]['register_subject'] >=1 && $rs[0]['register_subject'] <=9){
			$san['register_subject'] = intval($rs[0]['register_subject']);
		}
		return $san;
	}
	
	//更新用户的student_id
	public function update_stuid($user_id, $student_id){
		$this->db->query("update user set student_id=? where id=?", array($student_id, $user_id));
		return $this->db->affected_rows();
	}
	
	public function unbind_phone($user_id){
		$this->db->query("update user set phone_verified=0,phone_mask=NULL where id=?", array($user_id));
		return $this->db->affected_rows();
	}
	
	public function unbind_email($user_id){
		$this->db->query("update user set email_verified=0,email=NULL where id=?", array($user_id));
		return $this->db->affected_rows();
	}
	
	public function lock_user($user_id){
		$this->db->query("update user set is_lock=1 where id=?", array($user_id));
		return $this->db->affected_rows();
	}
}
/* End of file register_model.php */
/* Location: ./application/models/login/register_model.php */
