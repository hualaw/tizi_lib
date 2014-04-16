<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Session_Model extends LI_Model {
	
	private $_table="session";
	private $_api_table="session_api";

	function __construct()
	{
      	parent::__construct();
		$this->load->model("redis/redis_model");
		$this->load->helper("cookie");
		$this->load->library('encrypt');

		$this->load->model("question/question_subject_model");
		$this->load->model("user_data/student_data_model");
		$this->load->model("user_data/researcher_data_model");
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
				'student_id'=>$data['student_id'],
				'avatar'=>$register_data->avatar?$register_data->avatar:0,
				'certification'=>$register_data->certification?$register_data->certification:0,
				'register_subject'=>$this->question_subject_model->check_subject($register_data->register_subject,'binding')?$register_data->register_subject:0,
				'register_grade'=>$this->student_data_model->check_grade($register_data->register_grade)?$register_data->register_grade:0,
				'register_domain'=>$register_data->register_domain,
				'login_time'=>time()
			);
			
			//是否有答疑权限，有的话就显示答疑tab
			if($data['user_type'] == Constant::USER_TYPE_TEACHER) $user_data['aq_show']=$this->auth_aq($user_id);

			$this->session->set_userdata($user_data);

			if($switch_id) $data['switch_id']=$switch_id;
			if($dbsave) $this->db->insert($this->_table,$data);
			$this->load->library("credit");
			$this->credit->exec($user_id, "everyday_firstlogin", $user_data["certification"]);
			//if($this->db->affected_rows()==1) 
			$errorcode=true;
		}
		else
		{
			$errorcode=false;
		}
		return array('errorcode'=>$errorcode,'user_data'=>$user_data);
	}

	private function auth_aq($user_id)
	{
		$this->db->where('id',$user_id);
		$query=$this->db->get('aq_teacher');
		return $query->num_rows();
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

			$register_domain = '';
			if($user->user_type == Constant::USER_TYPE_RESEARCHER)
			{
				$researcher_data = $this->researcher_data_model->get_researcher_data($user_id);
				$register_domain = isset($researcher_data->domain_name)?$researcher_data->domain_name:'';
			}

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
				'user_agent'=>user_agent(),
				'generate_time'=>date("Y-m-d H:i:s"),
				'expire_time'=>'',
				'user_data'=>json_encode(
					array(
						'register_subject'=>$user->register_subject,
						'register_grade'=>$user->register_grade,
						'register_domain'=>$register_domain,
						'avatar'=>$user->avatar,
						'certification'=>$user->certification
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

	function clear_current_dir_cookie()
	{
		delete_cookie(Constant::COOKIE_CURRENT_CLOUD_DIR);
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

	public function generate_api_session($user_id,$api_type=Constant::API_TYPE_TIZI)
	{
		$session_id=sha1(md5($user_id).uniqid().mt_rand(1000000,5555555));
		$data=$this->bind_session($session_id,$user_id);
		$data['api_type']=$api_type;

		$this->db->where('user_id',$user_id);
		$this->db->where('api_type',$api_type);
		$query=$this->db->get($this->_api_table);
		if($query->num_rows() > 0)
		{
			$this->db->where('user_id',$user_id);
			$this->db->where('api_type',$api_type);
			$this->db->delete($this->_api_table); 
		}
		$this->db->insert($this->_api_table,$data);
		return $session_id;
	}
	
	public function get_api_session($session_id,$api_type=Constant::API_TYPE_TIZI,$select='')
	{
		if($select) $this->db->select($select);
		$this->db->where('session_id',$session_id);
		$this->db->where('api_type',$api_type);
		$query=$this->db->get($this->_api_table);
		return $query->row_array();
	}
}
/* End of file session_model.php */
/* Location: ./application/models/login/session_model.php */
