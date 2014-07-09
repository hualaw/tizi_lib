<?php

class Student_Paper_Model extends LI_Model{

	public function __construct(){

		parent::__construct();
	
	}

	public function update($s_paper_id, $data){
		
		$this->db->where('id', $s_paper_id);
		return $this->db->update('student_paper', $data); 

	}

	public function get_student_paper($user_id, $paper_id){
		
		return $this->db
			->query("select a.* from `student_paper` as a left join `homework_paper` as b on a.`paper_id` = b.`id` where a.`user_id` = {$user_id} and a.`paper_id` = {$paper_id}")
			->row_array();
		
	}

    public function connect_redis(){
		$this->load->model("redis/redis_model");
        if($this->redis_model->connect('timer'))
        {
			return $this->cache->redis;
        }
        return false;
    }


	

	

}
