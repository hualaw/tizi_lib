<?php

class Classes_agents_model extends LI_Model{
		
	public function __construct(){
		parent::__construct();
	}
	
	public function add_agents($province_id, $city_id, $county_id, $school_id){
		$this->db->query("INSERT IGNORE INTO classes_agents_student(province_id,city_id,county_id,school_id) VALUES(?,?,?,?)", 
			array($province_id, $city_id, $county_id, $school_id));
	}
	
	public function create($class_id, $school_id, array $students_name){
		$this->load->model("class/classes_student_create");
		$total = count($students_name);
		$start_id = $this->classes_student_create->get_stuid($total);
		$data = array();
		foreach ($students_name as $key => $value){
			$_priv = array(
				"password" => rand6pwd($class_id),
				"class_id" => $class_id,
				"student_id" => $start_id + $key,
				"student_name" => $value,
				"source" => 1
			);
			$data[] = $_priv;
		}
		foreach ($data as $value){
			$this->db->insert("classes_student_create", $value);
			$create_id = $this->db->insert_id();
			$agents = array(
				"school_id" => $school_id,
				"student_name" => $value["student_name"],
				"create_id" => $create_id
			);
			$this->db->insert("classes_agents_student", $agents);
		}
		return $data;
	}
	
	public function search($school_id, $student_name){
		$res = $this->db->query("SELECT * FROM classes_agents_student WHERE school_id=? AND student_name=?", array($school_id, $student_name))->result_array();
		return isset($res[0]) ? $res[0] : null;
	}
	
	public function register($create_id, $user_id, $active_date){
		$this->db->query("UPDATE classes_agents_student SET user_id=?,active_date=? WHERE create_id=?", array($user_id, $active_date, $create_id));
		return $this->db->affected_rows();
	}
	
	
}