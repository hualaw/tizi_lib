<?php

class Student_Paper_Model extends LI_Model{

	public function __construct(){

		parent::__construct();
	
	}

	public function update($s_paper_id, $data){
		
		$this->db->where('id', $s_paper_id);
		return $this->db->update('student_paper', $data); 

	}

	public function get_student_paper($user_id, $paper_assign_id = '', $limit = array()){
		
        $sql = "select a.*,b.`paper_id`,b.`start_time` as begin_time, b.`deadline`, b.`count`, b.`is_shuffled`, c.`subject_id`, d.`content` ,c.`user_id` as teacher_id ,b.`get_answer_way`,b.`name` as paper_name from `student_paper` as a left join `paper_assign` as b  on a.`paper_assign_id` = b.`id` left join `paper_testpaper` as c on b.`paper_id` = c.`id` left join `student_exercise_plan_comment` as d on a.`paper_assign_id` = d.`assignment_id` and a.`user_id` = d.`student_id` where a.`user_id` = {$user_id} and b.`is_assigned` = 1";

        if($paper_assign_id){

            $sql .= " and a.`paper_assign_id` = {$paper_assign_id}";
            return $this->db->query($sql)->row_array();

        }else{
        
            $sql .= " order by a.`id` desc";
            if($limit) {
                $sql .= " limit ".$limit[0]." , ".$limit[1];   
            }
            return $this->db->query($sql)->result_array();

        }
        
	}

    public function get_student_paper_num($user_id){
    
        $result = $this->db->query("select count(*) as paper_num from `student_paper` as a left join `paper_assign` as b on a.`paper_assign_id` = b.`id` where a.`user_id` = {$user_id} and b.`is_assigned` = 1")
            ->row_array();
        return isset($result['paper_num']) ? $result['paper_num'] : 0;
    }

    public function get_class_correct_rate($paper_assign_id){
            
        $data = $this->db->query("select `online_done_num`, `correct_num` from `student_paper` where `paper_assign_id` = {$paper_assign_id}")
            ->result_array();
        $correct_num = 0;
        $online_done_num = 0;
        foreach($data as $val){
            $correct_num += $val['correct_num'];
            if(!$online_done_num && $val['online_done_num'])
                $online_done_num = $val['online_done_num'];
        }
        $online_done_num = $online_done_num * count($data);

        return $online_done_num  ? $correct_num / $online_done_num : 0;

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
