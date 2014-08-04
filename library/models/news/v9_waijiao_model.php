<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class v9_waijiao_model extends MY_Model{
	
	public function addunits($name, $stage_id){
		$this->db->query("INSERT INTO fls_units(name,stage_id) VALUES(?,?)", array($name, $stage_id));
		return $this->db->insert_id();
	}
	
	public function delete_unit($id){
		$this->db->query("DELETE FROM fls_units WHERE id=?", array($id));
		return $this->db->affected_rows();
	}
	
	public function update_units($data, $id){
		$this->db->where(array("id" => $id));
		$this->db->update("fls_units", $data);
		return $this->db->affected_rows();
	}
	
	public function replace_units($id, $name, $stage_id, $order_list){
		$this->db->query("REPLACE INTO fls_units(id,name,stage_id,order_list) VALUES(?,?,?,?)", 
			array($id, $name, $stage_id, $order_list));
		return $this->db->affected_rows();
	}
	
	public function addvideo($video){
		$this->db->insert("fls_video", $video);
		return $this->db->insert_id();
	}
	
	public function updatevideo($data){
		$id = $data["id"];
		unset($data["id"]);
		$this->db->where(array("id" => $id));
		$this->db->update("fls_video", $data);
		return $this->db->affected_rows();
	}
	
	public function add_word($words){
		$this->db->insert("fls_words", $words);
		return $this->db->insert_id();
	}
	
	public function update_word($words){
		$id = $words["id"];
		unset($words["id"]);
		$this->db->where(array("id" => $id));
		$this->db->update("fls_words", $words);
		return $this->db->affected_rows();
	}
	
	public function add_exercise($data){
		$this->db->insert("fls_exercise", $data);
		return $this->db->insert_id();
	}
	
	public function update_exercise($data, $id){
		$this->db->where(array("id" => $id));
		$this->db->update("fls_exercise", $data);
		return $this->db->affected_rows();
	}
	
	public function add_video_exercise($exercise_id, $video_id){
		$this->db->query("INSERT IGNORE INTO fls_video_exercise(video_id,question_id) 
			VALUES({$video_id},{$exercise_id})");
		return $this->db->affected_rows();
	}
	
	public function setol($ol, $id){
		$this->db->where(array("id" => $id));
		$this->db->update("fls_video", array("online" => $ol));
		return $this->db->affected_rows();
	}
	
	public function add_student_video($data){
		$this->db->insert("student_video", $data);
		return $this->db->insert_id();
	}
	
	public function del_student_video($id){
		$this->db->query("DELETE FROM student_video WHERE id={$id} AND `online`=0");
		return $this->db->affected_rows();
	}
	
	public function del_waijiao_video_captions($video_id){
		$this->db->query("DELETE FROM fls_subtitle WHERE video_id={$video_id}");
		return $this->db->affected_rows();
	}
	
	public function insert_captions($data){
		$this->db->insert("fls_subtitle", $data);
		return $this->db->insert_id();
	}
	
	public function replace_captions($data){
		$this->db->query("REPLACE INTO fls_subtitle(id,video_id,begin_time,end_time,en_str,chs_str) 
			VALUES(?,?,?,?,?,?)", array($data["id"], $data["video_id"], $data["begin_time"], 
			$data["end_time"], $data["en_str"], $data["chs_str"]));
		return $this->db->affected_rows();
	}
	
}
/* end of v9_waijiao_model.php */