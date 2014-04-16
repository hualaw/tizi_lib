<?php

class credit_logs_model extends LI_Model {
	
	//最近100天积分增长记录
	public function recent_list($user_id, $offset, $persize){
		$res = $this->db->query("select id,foreign_id,credit_change,total,msg,`date` from credit_logs 
			where user_id=? and credit_change>0 order by id 
			desc limit {$offset},{$persize}", array($user_id))->result_array();
		return $res;
	}
	
	//最近100天积分增长记录统计
	public function count($user_id){
		$res = $this->db->query("select count(1) as num from credit_logs where user_id=? and credit_change>0", 
			array($user_id))->row_array();
		return $res["num"];
	}
	
	//最近100天积分的使用记录
	public function use_list($user_id, $offset, $persize){
		$res = $this->db->query("select id,foreign_id,credit_change,msg,`date` from credit_logs where user_id=? 
			and credit_change<0 order by id desc limit {$offset},{$persize}", array($user_id))->result_array();
	}
	
	//最近100天积分使用记录的统计
	public function use_count($user_id){
		$res = $this->db->query("select count(1) as num from credit_logs where user_id=? and credit_change<0");
		return $res["num"];
	}
	
	public function today_credit($user_id){
		$date = date("Y-m-d");
		$res = $this->db->query("select id,credit_change from credit_logs where user_id=? and 
			`date`>? and credit_change>0", array($user_id, $date))->result_array();
		$today_credit = 0;
		foreach ($res as $value){
			$today_credit += $value["credit_change"];
		}
		return $today_credit;
	}
}

/* end of credit_logs_model.php */