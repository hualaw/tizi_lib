<?php

class Classes_Schools extends LI_Model{
	
	/**
	 * 通过父节点获取所有孩子节点数据[area表]
	 * @param integer $parentid
	 * @param string $fields
	 * @return array
	 * @author jiangwuzhang
	 */
	public function id_children($parentid, $fields = "*"){
		$id_replace = array(2,25,27,32,33);
		$rel = array(
			2 => 52,
			25 => 321,
			27 => 343,
			32 => 394,
			33 => 395
		);
		if (in_array($parentid, $id_replace)){
			$parentid = $rel[$parentid];
		}
		$r = $this->db->query("select {$fields} from classes_area where 
				parentid=?", array($parentid))->result_array();
		return $r;
	}
	
	/** 
	 * 根据县/区的ID获取学校列表
	 * @param integer $county_id
	 * @param string $fields
	 * @return array
	 * @author jiangwuzhang
	 */
	public function county_schools($county_id, $sctype, $fields = "*"){
		$r = $this->db->query("select {$fields} from classes_schools where 
				county_id=? and sctype=?", array($county_id, $sctype))->result_array();
		return $r;
	}
	
	public function get_unuserdefine($county_id, $sctype, $fields = "*"){
		return $this->county_schools($county_id, $sctype, $fields = "*");
	}
	
	public function create($schoolname, $county_id, $sctype, $py, $first_py){
		$city_id = $this->parentid($county_id);
		$province_id = $this->parentid($city_id);
		$this->db->query("insert into classes_schools(county_id,schoolname,city_id,
			province_id,status,py,first_py,sctype) values(?,?,?,?,?,?,?,?)", array($county_id,
			$schoolname, $city_id, $province_id, 1, $py, $first_py, $sctype));
		return $this->db->affected_rows();
	}
	
	//自定义的学校
	public function define_create($schoolname, $province_id, $city_id, $county_id, $sctype, $property){
		$schoolname = trim($schoolname);
		$res = $this->db->query("select * from classes_schools_define where county_id=? and 
			schoolname=?", array($county_id, $schoolname))->row_array();
		if (isset($res["id"]) && $res["sctype"] == $sctype && $res["property"] == $property){
			$id = $res["id"];
		} else {
			$this->db->query("insert into classes_schools_define(county_id,schoolname,city_id,
				province_id,status,sctype,property) values(?,?,?,?,?,?,?)", array($county_id,
				$schoolname, $city_id, $province_id, 1, $sctype, $property));
			$id = $this->db->insert_id();
		}
		return $id;
	}
	
	public function update($id, $schoolname, $county_id, $sctype, $py, $first_py){
		$city_id = $this->parentid($county_id);
		$province_id = $this->parentid($city_id);
		$this->db->query("update classes_schools set county_id=?,city_id=?,province_id=?,
			schoolname=?,py=?,first_py=?,sctype=? where id=?", array($county_id, $city_id, 
			$province_id, $schoolname, $py, $first_py, $sctype, $id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 梯子班级管理迭代2.0
	 * 获取学校省事县和学校名称
	 */
	public function school_info($school_id){
		$result = $this->db->query("select province_id,city_id,county_id,schoolname from classes_schools 
			where id=?", array($school_id))->result_array();
		if (isset($result[0])){
			$school_info = array();
			$result = $result[0];
			$area = $this->db->query("select level,name from classes_area where id in(?,?,?)", 
				array($result["province_id"], $result["city_id"], $result["county_id"]))->result_array();
			foreach ($area as $value){
				if ($value["level"] == 1){
					$school_info["province"] = $value["name"];
				} else if ($value["level"] == 2){
					$school_info["city"] = $value["name"];
				} else if ($value["level"] == 3){
					$school_info["county"] = $value["name"];
				}
			}
			$school_info["school"] = $result["schoolname"];
			return $school_info;
		} else {
			return null;
		}
	}
	
	public function define_school_info($school_define_id, $moreinfo = false){
		$res = $this->db->query("select province_id,city_id,county_id,schoolname,sctype from 
			classes_schools_define where id=?", array($school_define_id))->result_array();
		if (isset($res[0])){
			$school_info = array();
			$res = $res[0];
			$area = $this->db->query("select level,name from classes_area where id in(?,?,?)", 
				array($res["province_id"], $res["city_id"], $res["county_id"]))->result_array();
			foreach ($area as $value){
				if ($value["level"] == 1){
					$school_info["province"] = $value["name"];
				} else if ($value["level"] == 2){
					$school_info["city"] = $value["name"];
				} else if ($value["level"] == 3){
					$school_info["county"] = $value["name"];
				}
			}
			$school_info["school"] = $res["schoolname"];
			if ($moreinfo === true){
				$school_info = array_merge($school_info, $res);
			}
			return $school_info;
		} else {
			return null;
		}
	}

	/**
	 * 通过class_id获取学校的相关信息
	 * @param integer $class_id
	 */
	public function getsh_info($class_id){
		$r = $this->db->query('select classname,province_id,city_id,county_id,school_id,class_grade,
				class_year,creator_id,school_define_id from classes where id=? and 
				close_status=?', array($class_id, 0))->result_array();
		if(!isset($r[0])){
			return null;
		}
		$sh = $r[0];
		$area_ids = $sh['province_id'];
		$area_ids .= $sh['city_id'] > 0 ? ','.$sh['city_id'] : '';
		$area_ids .= ','.$sh['county_id'];
		$r = $this->db->query("select id,name from classes_area where id in ({$area_ids})")->result_array();
		foreach ($r as $value){
			if ($value['id'] == $sh['province_id']){
				$sh['province'] = $value['name'];
			} else if ($value['id'] == $sh['city_id']){
				$sh['city'] = $value['name'];
			} else if ($value['id'] == $sh['county_id']){
				$sh['county'] = $value['name'];
			}
		}
		if ($sh["school_id"] > 0){
			$sh['schoolname'] = $this->id_school($sh['school_id']);
		} else {
			$sh["schoolname"] = $this->id_define_school($sh["school_define_id"]);
		}
		
		$this->load->model('class/classes_teacher');
		$sh['creator'] = $this->classes_teacher->teacher_realname($sh['creator_id']);
		$sh['creator_subject_id'] = $this->classes_teacher->teachsubj_inclass($class_id, $sh['creator_id']);
		
		return $sh;
	}

	/**
  	 * 通过学校ID获取学校名称
  	 */
  	public function id_school($school_id){
  		$r = $this->db->query('select schoolname from classes_schools where id=?', array($school_id))->result_array();
  		return isset($r[0]['schoolname']) ? $r[0]['schoolname'] : "";
  	}
  	
  	public function id_define_school($school_id){
		$res = $this->db->query("select schoolname from classes_schools_define where id=?", 
			array($school_id))->row_array();
  		return isset($row["schoolname"]) ? $res["schoolname"] : "";
	}

	public function get($school_id, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_schools where 
			id=?", array($school_id))->result_array();
		return isset($result[0]) ? $result[0] : null;
	}
	
	public function class_count($school_id){
		$result = $this->db->query("select count(*) as num from classes where school_id=?", 
			array($school_id))->result_array();
		return isset($result[0]["num"]) ? $result[0]["num"] : 0;
	}
	
	public function delete($id){
		$this->db->query("delete from classes_schools where id=?", array($id));
		return $this->db->affected_rows();
	}
	
	public function parentid($childid){
		$id_replace = array(52, 321, 343, 394, 395);
		$rel = array(
			52 => 2,
			321 => 25,
			343 => 27,
			394 => 32,
			395 => 33
		);
		if (in_array($childid, $id_replace)){
			return $rel[$childid];
		}
		$result = $this->db->query("select parentid from classes_area where id=?", array($childid))->result_array();
		return isset($result[0]["parentid"]) ? $result[0]["parentid"] : 0;
	}
	
	public function class_move($from_school_id, $to_school_id){
		$this->db->query("update classes set school_id=? where school_id=?", array($to_school_id, $from_school_id));
		return $this->db->affected_rows();
	}
	
}


/* end of classes_schools.php */