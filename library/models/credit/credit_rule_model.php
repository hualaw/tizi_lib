<?php

class credit_rule_model extends LI_Model {
	
	public function get_all(){
		$res = $this->db->query("select * from credit_rule order by id asc")->result_array();
		return $res;
	}
	
	public function action_get($action){
		$res = $this->db->query("select * from credit_rule where action=?", array($action))->row_array();
		return $res;
	}
	
	public function get_log($user_id, $rule_id){
		$res = $this->db->query("select * from credit_rule_logs where user_id=? and rule_id=? order 
			by id desc limit 0,1", array($user_id, $rule_id))->row_array();
		return $res;
	}
	
	public function delete_log($id){
		$this->db->query("delete from credit_rule_logs where id=?", array($id));
		return $this->db->affected_rows();
	}
	
}

/* end of credit_rule_model.php */