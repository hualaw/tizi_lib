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
	
	/**
	 * 获取一个老师加入的班级(联表查询)
	 */
	public function g_teacher_classinfo($user_id){
		$result = $this->db->query("select a.classname,a.school_id,a.class_year,a.stu_count,a.tch_count,
			a.create_date,a.creator_id,b.class_id,a.class_status from classes as a left join classes_teacher as b on 
			a.id=b.class_id where a.class_status=0 and b.teacher_id=? order by a.id desc", 
			array($user_id))->result_array();
		if (is_array($result)){
			$this->load->model("class/classes");
			foreach ($result as $key => $value){
				$result[$key]["creator_name"] = $this->classes->get_realname($value["creator_id"]);
				if ($value["school_id"] > 0){
					$this->load->model("class/classes_schools");
					$result[$key]["school_info"] = $this->classes_schools->school_info($value["school_id"]);
				}
			}
		}
		return $result;
	}
	
	/**
	 * 创建一条老师和班级的绑定信息，并更新班级老师数量
	 */ 
	public function i_join_class($class_id, $teacher_id, $subject_id, $join_date = time()){
		$total = $this->db->query("select count(*) as total from classes_teacher where class_id=? 
			and teacher_id=?", array($class_id, $teacher_id))->row();
		if ($total->total == 0){
			$this->db->trans_start();
			$this->db->query("insert into classes_teacher(class_id,teacher_id,subject_id,join_date) 
				values(?,?,?,?)", array($class_id, $teacher_id, $subject_id, $join_date));
			if ($this->db->affected_rows() === 1){
				$this->db->query("update classes set tch_count=tch_count+1 where id=?", array($class_id));
			}
			$this->db->trans_complete();
			if ($this->db->trans_status() === false){
				return -1;
			}
		}
		return 1;
	}
}

/* end of class_model.php */