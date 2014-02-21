<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Student_Model extends LI_Model {
	
	private $_table="student_reset_password";
	
	function __construct()
	{
       	parent::__construct();
		$this->load->database();
	}
	
	function save($uname,$student_id,$phone)
	{
		$insert_id = 0;
		$data=$this->bind_student($uname,$student_id,$phone);
		if($data)
		{
			$this->db->insert($this->_table,$data);
			$insert_id = $this->db->insert_id();
			if($insert_id > 0) $errorcode=true;
			else $errorcode=false;
		}
		else
		{
			$errorcode=false;
		}
		if(!$errorcode) log_message('error_tizi','17011:Student reset password send failed',$data);
		return array('id'=>$insert_id,'user_id'=>$data['user_id'],'uname'=>$uname,'student_id'=>$student_id,'errorcode'=>$errorcode);
	}

	function bind_student($uname,$student_id,$phone)
	{
		if(empty($student_id)&&empty($uname)) return false;
		if($student_id) $this->db->where('student_id',$student_id);
		if($uname) $this->db->where('uname',$uname);
		$query=$this->db->get('user');
		
		if($query->num_rows()==1)
		{
			$student=$query->row();
			$email=null;
			$data=array(  
				'user_id'=>$student->id,
				'uname'=>$uname,
				'student_id'=>$student_id,
				'email'=>$student->email,
				'phone'=>$phone,
				'name'=>$student->name,
				'submit_time'=>date("Y-m-d H:i:s"),
			);
		}
		else
		{
			$data=false;
		}
       	return $data;
	}
}
/* End of file student_model.php */
/* Location: ./application/models/login/student_model.php */
