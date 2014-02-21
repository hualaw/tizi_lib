<?php

class Classes_teacher_apply extends MY_Model{
	
	/**
	 * 老师申请加入某班级
	 * @param integer $class_id
	 * @param integer $teacher_id
	 * @param integer $subject_id
	 * @param integer $apply_date
	 * @return integer
	 * 	>0:申请成功，返回申请号ID
	 *   -5:已经在申请了
	 * @author jiangwuzhang
	 */
	public function teacher_apply($class_id, $teacher_id, $subject_id, $apply_date){
		
		//判断是否有正在申请的记录
		$result = $this->last_apply($class_id, $teacher_id);
		if ($result && $result["reply_date"] == 0){
			return -5;
		}
			
		//所有条件都满足，正常申请---->
		$this->db->query("insert into classes_teacher_apply(class_id,teacher_id,subject_id,
					apply_status,apply_date,reply_date) 
					values(?,?,?,?,?,?)", array($class_id, $teacher_id, $subject_id, 0, $apply_date, 0));
		return $this->db->insert_id();
	}
	
	/**
	 * 获取某教师申请加入某班级的最近一次记录
	 * @param integer $class_id
	 * @param integer $teacher_id
	 * @return array 一维数组
	 * @author jiangwuzhang
	 */
	public function last_apply($class_id, $teacher_id){
		$result = $this->db->query("select apply_status,apply_date,reply_date from 
					classes_teacher_apply where class_id=? and teacher_id=? order 
					by id desc limit 0,1", array($class_id, $teacher_id))->result_array();
		return isset($result[0]) ? $result[0] : array();
	}
	
	/**
	 * 获取某个老师有权限审核的申请
	 */ 
	public function t_apply($teacher_id, $limit = 300){
		$result = $this->db->query("select id from classes where creator_id=?", 
			array($teacher_id))->result_array();
		if (is_array($result)){
			foreach ($result as $value){
				$i_clsid[] = $value["id"];
			}
		}
		$t_apply = array();
		if (isset($i_clsid)){
			$str_clsid = implode(",", $i_clsid);
			$t_apply = $this->db->query("select a.id,a.class_id,a.teacher_id,a.subject_id,a.apply_date,b.name 
				from classes_teacher_apply as a left join user as b on a.teacher_id=b.id where a.class_id 
				in ({$str_clsid}) and a.apply_status=0 order by a.id desc limit 0,?", array($limit))->result_array();
			
		}

		if ($t_apply){
			foreach ($t_apply as $key => $value){
				$right = $this->db->query("select a.classname,b.schoolname from classes as a left join 
					classes_schools as b on a.school_id=b.id where a.id=?", array($value["class_id"]))->row();
				$t_apply[$key]["schoolname"] = $right->schoolname;
				$t_apply[$key]["classname"] = $right->classname;
			}
		}
		return $t_apply;
	}
	
	public function id_apply_classid($apply_id){
		$result = $this->db->query("select class_id,apply_status from classes_teacher_apply where 
				id=?", array($apply_id))->result_array();
		return isset($result[0]["apply_status"]) && $result[0]["apply_status"] == 0 ? $result[0]["class_id"] : 0;
	}
	
	/**
	 * 批准某个批准号加入班级
	 */ 
	public function accept($apply_id){
		$this->db->trans_start();
		$apply = $this->db->query("select * from classes_teacher_apply where id=?", array($apply_id))->row();
		if ($apply){
			$this->db->query("insert into classes_teacher(class_id,teacher_id,subject_id,join_date) 
				values(?,?,?,?)", array($apply->class_id, $apply->teacher_id, $apply->subject_id, time()));
			$this->db->query("update classes set tch_count=tch_count+1 where id=?", array($apply->class_id));
			$this->db->query("update classes_teacher_apply set apply_status=?,reply_date=? where id=?", 
				array(1, time(), $apply_id));
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		return true;
	}
	
	/**
	 * 拒绝某个批准号加入班级
	 */ 
	public function refuse($apply_id){
		$this->db->query("update classes_teacher_apply set apply_status=?,reply_date=? where id=?", 
			array(-1, time(), $apply_id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 获取申请的班级名称和人
	 */ 
	public function id_apply($apply_id){
		$data = $this->db->query("select a.teacher_id as user_id,b.classname from classes_teacher_apply 
			as a left join classes as b on a.class_id=b.id where a.id=?", array($apply_id))->result_array();
		return isset($data[0]) ? $data[0] : null;
	}
}
/* end of classes_teacher_apply.php */