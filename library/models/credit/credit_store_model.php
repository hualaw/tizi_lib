<?php

class credit_store_model extends LI_Model {
	
	public function product_all(){
		$res = $this->db->query("select id,name,credit_price,stock,exchange_total,thumb from 
			credit_store where online=1 and credit_type=0 order by id asc")->result_array();
		return $res;
	}
	
	public function get($id, $fields = "*"){
		$res = $this->db->query("select {$fields} from credit_store where id=? and online=1 and credit_type=0", array($id))->row_array();
		return $res;
	}
	
	public function pay($user_id, $goods_id){
		$goods = $this->get($goods_id);
		$this->load->model("credit/credit_model");
		$balance = $this->credit_model->get($user_id);
		if ($balance["balance"] >= $goods["credit_price"]){
			$this->db->trans_start();
			$this->db->query("UPDATE credit SET balance=balance-? WHERE id=?", array($goods["credit_price"], $user_id));
			$this->db->query("INSERT INTO credit_orders(user_id,goods_id,create_date) VALUES(?,?,?)", array(
				$user_id, $goods_id, date("Y-m-d H:i:s")));
			$foreign_id = $this->db->insert_id();
			
			$msg = "兑换".$goods["name"];
			$this->db->query("INSERT INTO credit_logs(user_id,foreign_id,credit_change,total,msg,cyclenum) VALUES(?,?,?,?,?,?)", array(
				$user_id, $foreign_id, 0-$goods["credit_price"], $balance["balance"]-$goods["credit_price"], $msg, 0));
			$this->db->query("UPDATE credit_store SET stock=stock-1,exchange_total=exchange_total+1 WHERE id=?", array($goods_id));
			$this->db->trans_complete();
			if ($this->db->trans_status() === false){
				return false;
			}
			return 1;
		} else {
			return -1;
		}
	}
	
	public function get_order($order_id, $fields = "*"){
		$res = $this->db->query("select {$fields} from credit_orders where id=?", array($order_id))->row_array();
		return $res;
	}
	
	public function update_addr($order_id, $address, $user_id){
		$this->db->query("update credit_orders set address=? where id=? and user_id=?", array($address, $order_id, $user_id));
		return $this->db->affected_rows();
	}
	
	public function spitdate_order_count($user_id, $date){
		$res = $this->db->query("select count(*) as num from credit_orders where user_id=? and create_date>?", 
			array($user_id, $date))->row_array();
		return isset($res["num"]) ? $res["num"] : 0;
	}
	
}

/* end of credit_store_model.php */