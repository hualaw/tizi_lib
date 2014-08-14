<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class v9_waijiao_model extends MY_Model{
	
	public function add_unit($edition_id, $stage_id, $prefix, $unit_name, $unit_number, $order_list, $category_id){
		$this->db->query("INSERT INTO common_unit(edition_id,stage_id,unit_number,unit_name,prefix,order_list,status,category_id) 
			VALUES(?,?,?,?,?,?,?,?)", array($edition_id, $stage_id, $unit_number, $unit_name, $prefix, $order_list, 0, $category_id));
		return $this->db->insert_id();
	}
	
	public function add_video_lesson($unit_id, $en_title, $chs_title, $length, $thumb_uri, $date, $online, $lesson_model){
		$this->db->query("INSERT INTO fls_video_lesson(unit_id,en_title,chs_title,length,thumb_uri,`date`,`online`,lesson_model) VALUES(?,?,?,?,?,?,?,?)", array($unit_id, 
			$en_title, $chs_title, $length, $thumb_uri, $date, $online, $lesson_model));
		return $this->db->insert_id();
	}
	
	public function update_video_lesson($TZID, $unit_id, $en_title, $chs_title, $length, $thumb_uri, $online, $lesson_model){
		$this->db->query("UPDATE fls_video_lesson SET unit_id=?,en_title=?,chs_title=?,length=?,thumb_uri=?,`online`=?,lesson_model=? 
			WHERE id=?", array($unit_id, $en_title, $chs_title, $length, $thumb_uri, $online, $lesson_model, $TZID));
		return $this->db->affected_rows();
	}
	
	public function set_lesson_resource_online($lesson_id){
		$this->db->query("UPDATE fls_lesson_resources SET `online`=0 WHERE lesson_id=?", array($lesson_id));
		return $this->db->affected_rows();
	}
	
	public function add_lesson_resource($data){
		unset($data["TZID"]);
		$this->db->insert("fls_lesson_resources", $data);
		return $this->db->insert_id();
	}
	
	public function update_lesson_resource($data){
		$id = $data["TZID"];
		unset($data["TZID"]);
		$this->db->where(array("id" => $id));
		$this->db->update("fls_lesson_resources", $data);
		return $this->db->affected_rows();
	}
	
	public function delete_subtitle_resids($res_ids){
		if (is_array($res_ids)){
			$implode = implode(",", $res_ids);
			$this->db->query("DELETE FROM fls_subtitle WHERE res_id IN ({$implode})");
			return $this->db->affected_rows();
		}
		return 0;
	}
	
	public function insert_subtitle($subtitle){
		if ($subtitle["TZID"] > 0){
			$subtitle["id"] = $subtitle["TZID"];
		}
		unset($subtitle["TZID"]);
		$this->db->insert("fls_subtitle", $subtitle);
		return $this->db->insert_id();
	}
	
	public function update_lesson_online($id, $online){
		$this->db->query("UPDATE fls_video_lesson SET `online`=? WHERE id=?", array($online, $id));
		return $this->db->affected_rows();
	}
	
	public function delete_words($lesson_id){
		$this->db->query("DELETE FROM fls_words WHERE lesson_id=?", array($lesson_id));
		return $this->db->affected_rows();
	}
	
	public function insert_word($word){
		if ($word["TZID"] > 0){
			$word["id"] = $word["TZID"];
		}
		unset($word["TZID"]);
		$this->db->insert("fls_words", $word);
		return $this->db->insert_id();
	}
	
	public function set_exercise_ol($lesson_id){
		$this->db->query("UPDATE fls_video_exercise AS a LEFT JOIN fls_exercise AS b ON a.question_id=b.id SET b.`online`=0 WHERE a.lesson_id=?", array($lesson_id));
		$this->db->query("DELETE FROM fls_video_exercise WHERE lesson_id=?", array($lesson_id));
	}
	
	public function update_exercise($exercise){
		$id = $exercise["TZID"];
		unset($exercise["TZID"]);
		unset($exercise["lesson_id"]);
		$this->db->where(array("id" => $id));
		$this->db->update("fls_exercise", $exercise);
		return $this->db->affected_rows();
	}
	
	public function insert_exercise($exercise){
		unset($exercise["TZID"]);
		unset($exercise["lesson_id"]);
		$this->db->insert("fls_exercise", $exercise);
		return $this->db->insert_id();
	}
	
	public function ignore_video_exercise($exercise_id, $lesson_id){
		$this->db->query("INSERT INTO fls_video_exercise(lesson_id,question_id) VALUES(?,?)", array($lesson_id, $exercise_id));
		return $this->db->insert_id();
	}
	
	public function get_all_edition(){
		$data = $this->db->query('SELECT * FROM common_edition')->result_array();
		return $data;
	}

	public function insert_edition($name, $subject_id, $category_id){
		$data = array(
			'name' => $name,
			'subject_id' => $subject_id,
			'category_id' => $category_id
		);
		$this->db->insert("common_edition", $data);
		return $this->db->insert_id();
	}

	public function add_stage_edition($stage_id, $edition_id, $img_url){
		$data = array(
			'stage_id' => $stage_id,
			'edition_id' => $edition_id,
			'img_url' => $img_url,
			'status' => 0
		);
		$this->db->insert("fls_stage_edition", $data);
		return $this->db->insert_id();
	}

	public function get_english_version($grade_type){
		if ($grade_type == 1){
			$subject_id = 21;
		} else if ($grade_type == 2){
			$subject_id = 3;
		} else if ($grade_type == 3){
			$subject_id = 12;
		}
		$data = $this->db->query("SELECT * FROM category WHERE subject_id IN ({$subject_id}) AND depth=1")->result_array();
		return $data;
	}

	public function get_next_child($pid){
		$data = $this->db->query("select node.* from category as node,category as parent
             where (node.lft between parent.lft and parent.rgt) and 
             	(node.depth = parent.depth + 1) and parent.id = {$pid} 
			order by node.list_order desc, node.lft")->result_array();
		return $data;
	}

	public function update_unit_listorders($order_list){
		foreach ($order_list as $value) {
			$this->db->query("UPDATE common_unit SET order_list=? WHERE id=?", array($value['order_list'], $value['id']));
		}
		return $this->db->affected_rows();
	}

	public function set_unit_status($unit_id, $status){
		$this->db->query("UPDATE common_unit SET status=? WHERE id=?", array($status, $unit_id));
		return $this->db->affected_rows();
	}

}
/* end of v9_waijiao_model.php */