<?php

class Classes extends LI_Model{
		
	public function __construct(){
		parent::__construct();
	}
	
	/**
	 * 旧的创建班级，2.0用于CRM的接口
	 * @param array $data
	 * 	classname as string
	 * 	creator_id as unsigned integer
	 * 	province_id as unsigned integer
	 * 	city_id as unsigned integer
	 * 	county_id as unsigned integer
	 * 	school_id as unsigned integer
	 * 	class_grade as unsigned integer
	 * 	class_year as unsigned integer
	 * 	create_date as unsigned integer
	 * 
	 * 	subject_id as tinyint	学科ID外键
	 * @return integer or boolean
	 * 	>0：班级添加成功，返回添加成功的班级ID
	 * 	-1：班级添加失败，该学校已存在同名的学校
	 * 	-2：班级添加失败，每个老师只能同时只能创建一个班级（仅在规定同一个老师只能创建一个班级的情况下适用）
	 *  false：班级添加失败，数据库错误
	 * @author jiangwuzhang
	 */
	public function create_class(array $data){
		extract($data);
		
		$this->load->model("class/classes_teacher");
		$created = $this->creator_get($creator_id);
		if (count($created) >= Constant::TEACHER_CLASS_MAX_NUM){
			return -2;
		}
		
		
		$binds = array(
			0 => $classname,
			1 => $creator_id,
			2 => $province_id,
			3 => $city_id,
			4 => $county_id,
			5 => $school_id,
			6 => $class_grade,
			7 => $class_year,
			8 => $create_date
		);
		$insert_status = $this->db->query("insert into classes(classname,creator_id,province_id,
					city_id,county_id,school_id,class_grade,class_year,
					create_date) values(?,?,?,?,?,?,?,?,?)", $binds);
		if (true === $insert_status){
			$class_id = $this->db->insert_id();
			/**
			 * 建立老师与班级的关系
			 */
			$this->db->query("insert into classes_teacher(class_id,teacher_id,subject_id,join_date) 
				values(?,?,?,?)", array($class_id, $creator_id, $subject_id, $create_date));
			return $class_id;
		}
		
		return false;
	}
	
	/**
	 * 迭代版2.0创建班级
	 */ 
	public function create($classname, $creator_id, $create_date, $subject_id, $extension = array()){
		$this->db->trans_start();
		$classes = array(
			"classname"		=> $classname,
			"creator_id"	=> $creator_id,
			"tch_count"		=> 1,
			"create_date"	=> $create_date
		);
		$classes = array_merge($classes, $extension);
		$this->db->insert("classes", $classes);
		if ($this->db->affected_rows() > 0){
			$class_id = $this->db->insert_id();
			$this->db->query("insert into classes_teacher(class_id,teacher_id,subject_id,join_date) 
				values(?,?,?,?)", array($class_id, $creator_id, $subject_id, $create_date));
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return -1;
		}
		return $class_id;
	}
	
	/**
	 * 获取班级基本信息
	 */
	public function get($class_id, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes where 
			id=?", array($class_id))->result_array();
		return isset($result[0]) ? $result[0] : null;
	}
	
	/**
	 * 获取创建者的真实姓名
	 */
	public function get_realname($user_id){
		$result = $this->db->query("select name from `user` where id=?", array($user_id))->result_array();
		return isset($result[0]["name"]) ? $result[0]["name"] : "";
	}
	
	/**
	 * 更新班级名称
	 */
	public function update_clsname($class_id, $classname, $teacher_id){
		$this->db->query("update classes set classname=? where id=? and 
			creator_id=?", array($classname, $class_id, $teacher_id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 更新班级入学年份
	 */ 
	public function update_year($class_id, $year, $teacher_id){
		$this->db->query("update classes set class_year=? where id=? and 
			creator_id=?", array($year, $class_id, $teacher_id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 更新班级所在学校
	 */ 
	public function update_school($class_id, $school_id, $school_info){
		$this->db->query("update classes set province_id=?,city_id=?,county_id=?,school_id=? 
			where id=?", array($school_info["province_id"], $school_info["city_id"], $school_info["county_id"], $school_id, $class_id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 判断是否是班级创始人
	 */ 
	public function is_creator($class_id, $teacher_id){
		$result = $this->get($class_id, "creator_id");
		if ($result["creator_id"] == $teacher_id){
			return true;
		}
		return false;
	}
	
	/**
	 * 解散一个班级
	 * 
	 * @param integer $class_id
	 * @return boolean
	 * @author jiangwuzhang
	 */
	public function disband($class_id){
		$this->db->trans_start();
		/*设置班级的class_status状态，invite状态*/
		$this->db->query("update classes set class_status=1,invitation='',tch_count=0,stu_count=0 where id=?", array($class_id));
		/*把所有的学生都踢出班级*/
		$this->db->query("delete from classes_student where class_id=?", array($class_id));
		/*把所有的班级创建的未登陆的学生帐号清空*/
		$this->db->query("delete from classes_student_create where class_id=? and user_id=0", array($class_id));
		/*把所有的老师踢出学校*/
		$this->db->query("delete from classes_teacher where class_id=?", array($class_id));
		/*把该班级的申请加入班级的记录清空*/
		$this->db->query("delete from classes_teacher_apply where class_id=?", array($class_id));
		$this->db->trans_complete();
		if (false === $this->db->trans_status()){
			return false;
		}
		return true;
	}
	
	public function creator_get($user_id, $fields = "*"){
		$data = $this->db->query("select {$fields} from classes where 
			creator_id=? and class_status=0", $user_id)->result_array();
		return $data;
	}
	
	//获取一个老师的static数据
	public function class_static($user_id){
		$res = $this->db->query("select a.class_id,b.stu_count from classes_teacher as a left join 
			classes as b on a.class_id=b.id where a.teacher_id=?", $user_id)->result_array();
		$this->load->model("class/classes_student_create");
		$data["class_total"] = count($res);
		$data["student_total"] = 0;
		foreach ($res as $value){
			$data["student_total"] += $value["stu_count"];
			$data["student_total"] += $this->classes_student_create->total($value["class_id"]);
		}
		return $data;
	}
}