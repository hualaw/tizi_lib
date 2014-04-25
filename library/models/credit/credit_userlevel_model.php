<?php

class credit_userlevel_model extends LI_Model {
	
	public function get_all(){
		$res = $this->db->query("select * from credit_userlevel order by id asc")->result_array();
		return $res;
	}
	
	//根据积分获取用户会员等级
	public function user_level($credit_total){
		$res = $this->db->query("select * from credit_userlevel where min_credit<=? and 
			max_credit>?", array($credit_total, $credit_total))->row_array();
		return $res;
	}
	
	public function get($user_id){
		$this->load->model("credit/credit_model");
		$credit = $this->credit_model->get($user_id);
		$user_level = $this->credit_userlevel_model->user_level($credit["total"]);
		$user_level["balance"] = $credit["balance"];
		$user_level["total"] = $credit["total"];
		return $user_level;
	}
}

/* end of credit_userlevel_model.php */