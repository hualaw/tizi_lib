<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class User_Invite_Model extends LI_Model {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function login($username, $password, $logb = "uname"){
		$logb_array = array("uname", "phone", "student_id");
		if (!in_array($logb, $logb_array)){
			return -1;
		}
		$res = $this->db->query("select id,password from user_invite where {$logb}=?", 
			array($username))->result_array();
		if (!isset($res[0])){
			return -1;
		}
		$invite = $res[0];
		if (md5("ti".$invite["password"]."zi") == $password){
			return $invite["id"];
		} else {
			return -1;
		}
	}
	
	public function get($id, $fields = "*"){
		$res = $this->db->query("select {$fields} from user_invite where 
			id=?", array($id))->result_array();
		return isset($res[0]) ? $res[0] : null;
	}
	
	public function update($id, $user_id, $active_time){
		$this->db->query("update user_invite set user_id=?,active_time=? where 
			id=?", array($user_id, $active_time, $id));
		return $this->db->affected_rows();
	}
	
	public function check($uname){
		if (preg_match("|^(tz).*|isU", $uname) || preg_match("|^(tizi).*|isU", $uname)){
			return true;
		}
		$res = $this->db->query("select id from user_invite where 
			uname=?", array($uname))->result_array();
		return isset($res[0]["id"]) ? true : false;
	}
}