<?php

class credit_model extends LI_Model {
	
	public function change_add($user_id, $foreign_id, $credit_change, $msg = "", $rule_log = array()){
		if ($credit_change <= 0){
			return -1;			
		}
		
		$this->db->trans_start();
		$sql_str["row_credit"] = "select id,balance,total from credit where id=?";
		$credit = $this->db->query($sql_str["row_credit"], array($user_id))->row_array();
		if (!isset($credit["balance"])){
			$sql_str["update_credit"] = "insert into credit(id,balance,total) values(?,?,?)";
			$this->db->query($sql_str["update_credit"], array($user_id, $credit_change, $credit_change));
		} else {
			$balance = $credit["balance"] + $credit_change;			//帐变后积分剩余
			$total = $credit["total"] + $credit_change;
			$sql_str["update_credit"] = "UPDATE credit SET balance=?,total=? WHERE id=?";
			$this->db->query($sql_str["update_credit"], array($balance, $total, $user_id));
		}
		if ($this->db->affected_rows() === 1){
			if (!isset($total) or !$total){
				$total = $credit_change;
			}
			
			/**
			 * 改为一个操作一天只产生一个log
			$this->db->query("INSERT INTO credit_logs(user_id,foreign_id,credit_change,total,msg) 
				VALUES(?,?,?,?,?)", array($user_id, $foreign_id, $credit_change, $total, $msg));
			*/
			$date = date("Y-m-d");
			$credit_log = $this->db->query("select id,credit_change,date from credit_logs where user_id=? 
				and foreign_id=? and date>?", array($user_id, $foreign_id, $date))->row_array();
			if (isset($credit_log["id"])){
				$i_credit_change = $credit_log["credit_change"] + $credit_change;
				$this->db->query("update credit_logs set credit_change=?,total=?,cyclenum=?,`date`=CURRENT_TIMESTAMP() where id=?", 
					array($i_credit_change, $total, $rule_log["cyclenum"], $credit_log["id"]));
			} else {
				$this->db->query("insert into credit_logs(user_id,foreign_id,credit_change,total,msg,cyclenum) 
					values(?,?,?,?,?,?)", array($user_id, $foreign_id, $credit_change, $total, $msg, $rule_log["cyclenum"]));
			}
			
			if ($this->db->affected_rows() === 1){
				if (isset($rule_log["id"])){
					$this->db->where("id", $rule_log["id"]);
					unset($rule_log["id"]);
					$this->db->update("credit_rule_logs", $rule_log); 
				} else {
					$this->db->insert("credit_rule_logs", $rule_log);
				}
				if ($this->db->affected_rows() !== 1){
					$this->db->trans_rollback();
				}
			} else {
				$this->db->trans_rollback();
			}
		} else {
			$this->db->trans_rollback();
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		return true;
	}
	
	public function get($user_id){
		$res = $this->db->query("select balance,total from credit where id=?", array($user_id))->row_array();
		if (!isset($res["balance"])){
			$res["balance"] = 0;
			$res["total"] = 0;
			$this->db->query("insert into credit(id,balance,total) values(?,?,?)", 
				array($user_id, $res["balance"], $res["total"]));
		}
		return $res;
	}
	
}

/* end of credit_model.php */