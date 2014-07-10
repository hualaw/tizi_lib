<?php

class credit_store_model extends LI_Model {
	
	public function product_all(){
		$res = $this->db->query("select id,name,credit_price,stock,exchange_total,thumb from 
			credit_store order by id asc")->result_array();
		return $res;
	}
	
	public function get($id, $fields = "*"){
		$res = $this->db->query("select {$fields} from credit_store where id=?", array($id))->row_array();
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
	
	public function update_addr($addr_id, $order_id, $user_id){
		$this->db->query("update credit_orders set address_id=? where id=? and user_id=?", array($addr_id, $order_id, $user_id));
		return $this->db->affected_rows();
	}
	
}

/* end of credit_store_model.php */