<?php

class Teacher_Data_Model extends CI_Model {

    private $_table='user_teacher_data';

    function __construct()
    {
        parent::__construct();
    }

    public function get_teacher_data($user_id)
    {
        $this->db->where('user_id',$user_id);
        $query=$this->db->get($this->_table);
        return $query->row();
    }

    public function update_teacher_download_default($user_id,$download_default,$download_type='paper')
    {
        if(!$download_default) return false;
        return $this->update_teacher_data($user_id,$download_default,$download_type.'_download_default');
    }

    private function update_teacher_data($user_id,$data_value,$data_name)
    {
        if(empty($data_name)) return false;

        $teacher_data=$this->get_teacher_data($user_id);

        if(empty($teacher_data))
        {
            $this->db->insert($this->_table,array('user_id'=>$user_id,$data_name=>$data_value));
            if($this->db->affected_rows()) return $this->db->insert_id();
        }
        else
        {
            if($teacher_data->{$data_name}===$data_value) return true;
            
            $this->db->where('user_id',$user_id);
            $this->db->update($this->_table,array($data_name=>$data_value));
            if($this->db->affected_rows()) return true;
        }
        return false;
    }

}
