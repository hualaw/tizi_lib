<?php

class Classes_agents_model extends LI_Model{
	
	const HOMEPAGE_AGENT_PROVINCE = "homepage_agent_province";
	
	private $replace_data;
	
	public function __construct(){
		parent::__construct();
		$this->replace_data = array(
			2 => 52,
			25 => 321,
			27 => 343,
			32 => 394,
			33 => 395
		);
	}
	
	public function add_agents($province_id, $city_id, $county_id, $school_id){
		$this->db->query("INSERT IGNORE INTO classes_agents(province_id,city_id,county_id,school_id) VALUES(?,?,?,?)", 
			array($province_id, $city_id, $county_id, $school_id));
		return $this->db->affected_rows();
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
				"username" => $value["student_name"],
				"create_id" => $create_id
			);
			$this->db->insert("classes_agents_user", $agents);
		}
		return $data;
	}
	
	public function search($school_id, $student_name){
		$res = $this->db->query("SELECT * FROM classes_agents_user WHERE school_id=? AND username=?", array($school_id, $student_name))->result_array();
		return isset($res[0]) ? $res[0] : null;
	}
	
	public function register($create_id, $user_id, $active_date){
		$this->db->query("UPDATE classes_agents_user SET user_id=?,active_date=? WHERE create_id=?", array($user_id, $active_date, $create_id));
		return $this->db->affected_rows();
	}
	
	/*直接绑定加入*/
	public function abs_sign($user_id, $school_id, $name){
		$this->db->query("INSERT IGNORE INTO classes_agents_user(school_id,username,user_id) VALUES(?,?,?)", array($school_id, $name, $user_id));
		return $this->db->affected_rows();
	}
	
	public function get_province(){
		$fields = "province";
		$this->load->model("redis/redis_model");
        $data = array();
    	if($this->redis_model->connect("statistics")){
			$data = $this->cache->hget(self::HOMEPAGE_AGENT_PROVINCE, $fields);
			if ($data){
				$data = json_decode($data, true);
			}
		}
		if (!$data or $data["last_update"] != date("Y-m-d")){
			$data["data"] = self::func_get_province();
			$data["last_update"] = date("Y-m-d");
            if($this->redis_model->connect("statistics")){
                $this->cache->hset(self::HOMEPAGE_AGENT_PROVINCE, $fields, json_encode($data));
            }
		}
		return $data;
	}
	
	public function func_get_province(){
		$res = $this->db->query("SELECT a.province_id,b.name FROM `classes_agents` AS a LEFT JOIN `classes_area` AS b ON a.province_id=b.id 
					GROUP BY a.province_id")->result_array();
		return $res;
	}
	
	public function get_city($province_id){
		$cities = array();
		if (array_key_exists($province_id, $this->replace_data)){
			$province_id = $this->replace_data[$province_id];
			$res = $this->db->query("SELECT a.county_id as city_id,b.name FROM classes_agents as a left join classes_area as b on a.county_id=b.id 
						 WHERE a.city_id=?", array($province_id))->result_array();
			foreach ($res as $value){
				$cities[$value["city_id"]] = $value["name"];
			}
		} else {
			$res = $this->db->query("SELECT a.city_id,b.name FROM classes_agents as a left join classes_area as b on a.city_id=b.id 
						 WHERE a.province_id=?", array($province_id))->result_array();
			
			foreach ($res as $value){
				$cities[$value["city_id"]] = $value["name"];
			}
		}
		return $cities;
	}
	
	public function get_school($city_id){
		$res = $this->db->query("SELECT a.school_id,b.schoolname FROM classes_agents as a left join classes_schools as b on a.school_id=b.id 
					 WHERE a.city_id=?", array($city_id))->result_array();
		return $res;
	}
	
	public function get_school_county($county_id){
		$res = $this->db->query("SELECT a.school_id,b.schoolname FROM classes_agents as a left join classes_schools as b on a.school_id=b.id 
					 WHERE a.county_id=?", array($county_id))->result_array();
		return $res;
	}
	
	public function name_school($school_id){
		$data = array();
		$res = $this->db->query("SELECT username FROM classes_agents_user where school_id=?", array($school_id))->result_array();
		foreach ($res as $value){
			$data[] = $value["username"];
		}
		return $data;
	}
	
	public function get_by_school_id($school_id, $fields = "*"){
		$res = $this->db->query("SELECT {$fields} FROM classes_agents WHERE school_id=?", $school_id)->row_array();
		return $res;
	}
	
	public function is_agents_user($user_id){
		$res = $this->db->query("SELECT id FROM classes_agents_user WHERE user_id=?", $user_id)->row_array();
		return isset($res["id"]) ? true : false;
	}
	
}