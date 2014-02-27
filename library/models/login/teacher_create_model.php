<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class Teacher_Create_Model extends LI_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function login($username, $password){
		$this->load->model("login/user_invite_model");
		return $this->user_invite_model->login($username, $password);
	}
	
	public function get($id, $fields = "*"){
		$res = $this->db->query("select {$fields} from teacher_create where 
			id=?", array($id))->result_array();
		return isset($res[0]) ? $res[0] : null;
	}
	
	public function update($id, $user_id, $active_time){
		$this->db->query("update teacher_create set user_id=?,active_time=? where 
			id=?", array($user_id, $active_time, $id));
		return $this->db->affected_rows();
	}
	
	public function check($uname){
		if (preg_match("|^(tz).*|isU", $uname) || preg_match("|^(tizi).*|isU", $uname)){
			return true;
		}
		$res = $this->db->query("select id from teacher_create where 
			uname=?", array($uname))->result_array();
		return isset($res[0]["id"]) ? true : false;
	}
}