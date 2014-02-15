<?php

class class_model extends LI_Model {
	
	/**
	 * 获取一个老师加入的班级(不联表)
	 */ 
	public function g_teacher_class($user_id, $fields = "*", $order = "class_id DESC"){
		$res = $this->db->query("select {$fields} from classes_teacher where 
			teacher_id=? order by {$order}", array($user_id))->result_array();
		return $res;
	}
	
	/**
	 * 获取一个班级的所有老师(不联表)
	 */
	public function g_class_teacher($class_id, $fields = "*"){
		$res = $this->db->query("select {$fields} from classes_teacher where 
			class_id=?", array($class_id))->result_array();
		return $res;
	}
	
	/**
	 * 获取班级基本信息
	 */
	public function g_classinfo($class_id, $fields = "*"){
		$res = $this->db->query("select {$fields} from classes where 
			id=?", array($class_id))->result_array();
		return isset($res[0]) ? $res[0] : null;
	}
}

/* end of class_model.php */