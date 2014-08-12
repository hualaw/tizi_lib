<?php

class Student_Zuoye_Model extends MY_Model {

    public function __construct(){

        parent::__construct();
        
    }

    public function get($where, $fields = '', $limit=array()){
        if(empty($fields)){
            $fields = 'zuoye_student.*, zuoye_assign.video_ids, zuoye_assign.user_id as teacher_id, zuoye_assign.unit_game_ids,zuoye_assign.start_time as assign_start_time,zuoye_assign.end_time as assign_end_time,zuoye_assign.unit_ids, ,zuoye_assign.subject_id,zuoye_assign.paper_ids,zuoye_comment.content';
        }
        $where['zuoye_assign.`status`'] = 1;
        $this->db->select($fields);

        $this->db->from('zuoye_student');
        $this->db->join('zuoye_assign', 'zuoye_assign.id = zuoye_student.zy_assign_id', 'left');
        $this->db->join('zuoye_comment', 'zuoye_student.zy_assign_id = zuoye_comment.zy_assign_id and zuoye_student.user_id = zuoye_comment.user_id', 'left');
        $this->db->where($where);
        $this->db->order_by("zuoye_student.id", "desc"); 
        if(!empty($limit)){
            call_user_func_array(array($this->db, 'limit'), array_reverse($limit));
        }
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

    public function checkCompleteStatus($uid, $zuoye_id) {
               
        $data = $this->get(array('zuoye_student.id'=>$zuoye_id));
        $total_num = $complete_num = 0;
        if (isset($data[0]) && !empty($data[0])) {
            $student_zuoye = $data[0];
            $zuoye_info = !empty($student_zuoye['zuoye_info']) ? 
                json_decode($student_zuoye['zuoye_info'], true) : array();
            if (!empty($student_zuoye['video_ids'])) {
                $total_num += count(explode(',', $student_zuoye['video_ids']));
            }
            if (!empty($student_zuoye['unit_game_ids'])) {
                $total_num += count(json_decode($student_zuoye['unit_game_ids'])); 
            }
            if (!empty($student_zuoye['paper_ids'])) {
                $paper_ids = json_decode($student_zuoye['paper_ids'], true);
                $total_num += count($paper_ids);
                foreach($paper_ids as $paper) {
                    $assign_id = $paper['assignment_id'];
                }
            }
            if (!empty($zuoye_info)) {
                if (isset($zuoye_info['game'])) {
                    $complete_num += count($zuoye_info['game']);
                }
                if (isset($zuoye_info['video'])) {
                    $complete_num += count($zuoye_info['video']);
                }
            }
            

        }
        return false;

        
    }
        
    


}
