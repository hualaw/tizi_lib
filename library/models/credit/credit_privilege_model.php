<?php

class credit_privilege_model extends LI_Model {
	
	public function get_all(){
		$res = $this->db->query("select * from credit_privilege order by id asc")->result_array();
		return $res;
	}
	
	public function get($userlevel_id){
		$res = $this->db->query("select * from credit_privilege where userlevel_id=? order by id 
			asc", array($userlevel_id))->result_array();
		return $res;
	}
	
}

/* end of credit_privilege_model.php */
