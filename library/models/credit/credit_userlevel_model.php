<?php

class credit_userlevel_model extends LI_Model {
	
	public function get_all(){
		$res = $this->db->query("select * from credit_userlevel order by id asc")->result_array();
		return $res;
	}
	
}

/* end of credit_userlevel_model.php */