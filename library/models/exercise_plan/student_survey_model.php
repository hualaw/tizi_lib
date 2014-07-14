<?php

class Student_Survey_Model extends LI_Model{

    private $_tb_name;

    public function __construct(){

        parent::__construct();
        $this->_tb_name = 'student_survey';

    }

    public function add($data){

        $query = $this->db
            ->query("select `id` from {$this->_tb_name} where `survey_id` = {$data['survey_id']}");
        if ($query->num_rows())
        {
            $result = $query->row_array();
            $this->db->where('survey_id', $data['survey_id']);
            $this->db->update(
                $this->_tb_name,
                $data
            );
            return $result['id'];
        }else{
            $data['created_date'] = time();
            if($this->db->insert(
                $this->_tb_name,
                $data
            )){
                return $this->db->insert_id();
            }
            return false;
        }

    }

    public function getData($id, $uid){
               
        return $this->db
            ->query("select a.*,b.`is_submit` from {$this->_tb_name} as a
            left join `student_survey_info` as b on a.`id` = b.`student_survey_id`
            where a.`id` = {$id} and b.`uid` = {$uid}")
            ->row_array();

    }

    public function survey_submit($id, $uid){
    
        $result = $this->db
            ->query("select `id` from `student_survey` where `survey_id` = {$id}")
            ->row_array();
        if(isset($result['id'])){
            $student_survey_id = $result['id'];
            return $this->db
                ->query("update `student_survey_info` set `is_submit` = 1 where `student_survey_id` = {$student_survey_id} and `uid` = {$uid}");
        }else{
            return false;
        }

    }











}
