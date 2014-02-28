<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class classes_notify extends LI_Model{
	
	/**
	 * 获取某人的notify
	 */ 
	public function get($user_id, $fields = "*"){
		$data = $this->db->query("select {$fields} from classes_notify where user_id=? order by id desc", 
			array($user_id))->result_array();
		return $data;
	}
	
	/**
	 * 设置一条消息为已读
	 */ 
	public function set($id){
		$this->db->query("update classes_notify set user_id=0-user_id where id=?", array($id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 按id获取某条
	 */ 
	public function id($id, $fields = "*"){
		$data = $this->db->query("select {$fields} from classes_notify where id=?", 
			array($id))->result_array();
		return isset($data[0]) ? $data[0] : null;
	}
	
	/**
	 * 添加一条消息
	 */ 
	public function add($user_id, $msg, $date){
		$this->db->query("insert into classes_notify(user_id,msg,date) values(?,?,?)", 
			array($user_id, $msg, $date));
		return $this->db->affected_rows();
	}
}
/* end of classes_notify.php */