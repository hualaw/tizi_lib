<?php

class Student_Zuoye_Model extends MY_Model {

    public function __construct(){

        parent::__construct();
        
    }

    public function get($where){

        $this->db->select('zuoye_student.*, zuoye_assign.video_ids, zuoye_assign.unit_game_ids,zuoye_assign.start_time,zuoye_assign.end_time, zuoye_comment.content');
        $this->db->from('zuoye_student');
        $this->db->join('zuoye_assign', 'zuoye_assign.id = zuoye_student.zy_assign_id', 'left');
        $this->db->join('zuoye_comment', 'zuoye_student.zy_assign_id = zuoye_comment.zy_assign_id and zuoye_student.user_id = zuoye_comment.user_id', 'left');
        $this->db->where($where);
        return $this->db->get()
            ->result_array();

    }

    public function fetchAll($uid){
        
        $data = $this->db
            ->query("select * from `zuoye_student` as a left join `zuoye_assign` as b on a.`zy_assign_id` = b.`id` where a.`user_id` = {$uid}")
            ->result_array();
        
        return $data;

    }

    public function update($zuoye_id, $data){

        $this->db->where('id', $zuoye_id);

        return $this->db->update('zuoye_student', $data); 
        
    }
        
    


}
