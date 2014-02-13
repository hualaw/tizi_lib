<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Session_Model extends LI_Model {
	
	private $_table="session";

	function __construct()
	{
      	parent::__construct();
		$this->load->model("redis/redis_model");
		$this->load->helper("cookie");
		$this->load->library('encrypt');

		$this->load->model("question/question_subject_model");
		$this->load->model("user_data/student_data_model");
	}

	/*desc:generate session after login*/
	/*input:arg($user_id)*/
	/*output:session,return(errorcode(1-success,0-failed))*/
	function generate_session($user_id,$switch_id=false,$dbsave=true)
	{
		$session_id=$this->session->userdata("session_id");
		$data=$this->bind_session($session_id,$user_id);
		if(!empty($data))
		{
			$register_data=json_decode($data['user_data']);
			$user_data=array(
				'user_id'=>$user_id,
				'urname'=>$data['name'],
				'user_type'=>$data['user_type'],
				'uname'=>$data['uname'],
				'avatar'=>$register_data->avatar?$register_data->avatar:0,
				'register_subject'=>$this->question_subject_model->check_subject($register_data->register_subject,'binding')?$register_data->register_subject:0,
				'register_grade'=>$this->student_data_model->check_grade($register_data->register_grade)?$register_data->register_grade:0,
				'login_time'=>time()
			);
			
			$this->session->set_userdata($user_data);

			if($switch_id) $data['switch_id']=$switch_id;
			if($dbsave) $this->db->insert($this->_table,$data);
			//if($this->db->affected_rows()==1) 
			$errorcode=true;
		}
		else
		{
			$errorcode=false;
		}
		return array('errorcode'=>$errorcode);
	}

	function clear_session()
	{
		$this->session->sess_destroy();
	}

	function bind_session($session_id,$user_id)
	{	
		$data=array();
		$this->db->where('id',$user_id);
		$query=$this->db->get('user');
		if($query->num_rows()==1)
		{
			$user=$query->row();
			$name=$user->name;
			if(!$name) $name=$user->phone_mask;
			if(!$name) $name=$user->email;

			$data=array(	
				'session_id'=>$session_id,
				'user_id'=>$user_id,
				'user_type'=>$user->user_type,
				'email'=>$user->email,
				'phone'=>$user->phone_mask,
				'uname'=>$user->uname,
				'name'=>$name,
				'student_id'=>$user->student_id,
				'ip'=>ip2long(get_remote_ip()),
				'generate_time'=>date("Y-m-d H:i:s"),
				'expire_time'=>'',
				'user_data'=>json_encode(
					array(
						'register_subject'=>$user->register_subject,
						'register_grade'=>$user->register_grade,
						'avatar'=>$user->avatar
					)
				)
			);
		}	
		return $data;
	}

	/*desc:generate cookie after login*/
	/*input:arg($username,$user_id)*/
	/*output:cookie,return(errorcode(1-success,0-failed)*/
	function generate_cookie($username,$user_id,$expire_time=Constant::COOKIE_EXPIRE_TIME)
	{
		$username=$this->encrypt->encode($username.time());
		$this->input->set_cookie(Constant::COOKIE_TZUSERNAME,$username,$expire_time);

		if($this->redis_model->connect('auto_login'))
		{
			$login_value=json_encode(array('user_id'=>$user_id,'expire_time'=>$expire_time));
			if($expire_time < Constant::REDIS_AUTHLOGIN_TIMEOUT) $expire_time = Constant::REDIS_AUTHLOGIN_TIMEOUT;
			$this->cache->save($username,$login_value,$expire_time);
		}
		return array('errorcode'=>true);		
	}

	function clear_mscookie()
	{
		delete_cookie(Constant::COOKIE_TZMYSUBJECT_PAPER);
		delete_cookie(Constant::COOKIE_TZMYSUBJECT_DOC);
		delete_cookie(Constant::COOKIE_TZMYSUBJECT_HOMEWORK);
		return array('errorcode'=>true);
	}
	
	function clear_cookie()
	{
		if($this->redis_model->connect('auto_login'))
		{
			$username=$this->input->cookie(Constant::COOKIE_TZUSERNAME);
			$this->cache->delete($username);
		}

		delete_cookie(Constant::COOKIE_TZUSERNAME);
		return array('errorcode'=>true);
	}

	public function get_lastgen($user_id){
		$result = $this->db->query("select generate_time from `session` where user_id=? order by 
			id desc limit 0,1", array($user_id))->result_array();
		if (isset($result[0]["generate_time"])){
			return $result[0]["generate_time"];
		}
		return null;
	}
}
/* End of file session_model.php */
/* Location: ./application/models/login/session_model.php */