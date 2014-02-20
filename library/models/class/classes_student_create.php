<?php
class classes_student_create extends MY_Model{
	
	/**
	 * 学号发生器
	 * @param integer $num
	 * @return integer
	 * 	返回学号ID的起始值
	 */
	public function get_stuid($num = 1){
		$this->db->query("LOCK TABLES classes_stuid READ");
		$this->db->query("LOCK TABLES classes_stuid WRITE");
		$r = $this->db->query("select id from classes_stuid")->result_array();
		$this->db->query("update classes_stuid set id=id+?", array($num));
		$this->db->query("UNLOCK TABLES");
		return intval($r[0]["id"] + 1);
	}
	
	/**
	 * 创建学生帐号
	 */ 
	public function create($class_id, array $student_names){
		$student_count = count($student_names);
		$student_id_start = $this->get_stuid($student_count);
		$rand = array();
		foreach ($student_names as $key => $value){
			$rand[$key]["password"] = rand6pwd($class_id);
			$rand[$key]["class_id"] = $class_id;
			$rand[$key]["student_id"] = $student_id_start + $key;
			$rand[$key]["student_name"] = $value;
		}
		$this->db->insert_batch("classes_student_create", $rand);
		return $rand;
	}
	
	/**
	 * 获取班级内所有未登陆过的学生帐号密码
	 */
	public function get($class_id, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_student_create where class_id=? 
			and user_id=0 order by id asc", array($class_id))->result_array();
		return $result;
	}
	
	/**
	 * 通过一个student_id获取一条数据
	 */ 
	public function studentid_get($student_id, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_student_create where student_id=?", 
			array($student_id))->result_array();
		return isset($result[0]) ? $result[0] : null;
	}
	
	/**
	 * 通过student_id删除一个帐号
	 */ 
	public function remove($student_id){
		$this->db->query("delete from classes_student_create where student_id=? and user_id=0", 
			array($student_id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 创建学生帐号，class_id=0，并有data数据
	 */ 
	public function create_prepare($student_info, $class_id){
		$student_count = count($student_info);
		$student_id_start = $this->get_stuid($student_count);
		$rand = array();
		$student_info = array_values($student_info);
		foreach ($student_info as $key => $value){
			$rand[$key]["password"] = rand6pwd($class_id);
			$rand[$key]["class_id"] = $class_id;
			$rand[$key]["student_id"] = $student_id_start + $key;
			$rand[$key]["student_name"] = $value["name"];
			unset($value["name"]);
			$rand[$key]["extension"] = json_encode($value);
			$this->db->insert("classes_student_create", $rand[$key]);
			$rand[$key]["cid"] = $this->db->insert_id();
		}
		return $rand;
	}
	
	/**
	 * 创建的学生表验证
	 * @param stirng $student_id
	 * @param string $password
	 * @return
	 * 	>0:返回classes_student_create表的主键
	 * 	-1:验证失败，无该学生的登陆信息
	 */
	public function login($student_id, $password){
		$rs = $this->db->select("id,password")->from("classes_student_create")->where("student_id",$student_id)->get()->row();
		if(isset($rs->password) && md5("ti".$rs->password."zi") == $password){
			return $rs->id;
		} else {
			return -1;
		}
	}
	
	public function id_create($pk, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_student_create where id=?", 
			array($pk))->result_array();
		return isset($result[0]) ? $result[0] : null;
	}
	
	public function signed($pk, $user_id, $active_time){
		$this->db->query("update classes_student_create set user_id=?,
			active_time=? where id=?", array($user_id, $active_time, $pk));
		return $this->db->affected_rows();
	}
	
	public function get_extension($user_id){
		$res = $this->db->query("select extension from classes_student_create where user_id=?", 
			array($user_id))->result_array();
		if (isset($res[0]["extension"])){
			$extension = json_decode($res[0]["extension"], true);
		} else {
			$extension = array();
		}
		return $extension;
	}
	
	/**
	 * 获取所有未登陆且有学生姓名的学生
	 */ 
	public function get_hadname($class_id){
		$res = $this->db->query("select id,student_id,student_name from classes_student_create 
			where class_id=? and user_id=0 and student_name!=''", array($class_id))->result_array();
		return $res;
	}
	
	public function total($class_id){
		$res = $this->db->query("select count(*) as num from classes_student_create where 
			class_id=?", array($class_id))->result_array();
		return intval($res[0]["num"]);
	}
}