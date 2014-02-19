<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class Apps_Oauth_Model extends LI_Model {
	
	public function __construct() {
		
	}
	
	public function set($user_id, $data){
		$oauth = sha1(md5($user_id).uniqid().mt_rand(1000000,5555555));
		$data = serialize($data);
		$this->db->query("replace into apps_oauth(oauth,user_id,data,login_time) 
			values(?,?,?,?)", array($oauth, $user_id, $data, time()));
		return $oauth;
	}
	
	public function get($oauth){
		$result = $this->db->query("select data from apps_oauth where 
			oauth=?", array($oauth))->result_array();
		return isset($result[0]["data"]) ? unserialize($result[0]["data"]) : null;
	}
	
}
/* End of file register_model.php */
/* Location: ./application/models/login/register_model.php */
