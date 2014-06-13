<?php
require_once('data_model.php');

class Teacher_Data_Model extends Data_Model {

    protected $_table='user_teacher_data';

    function __construct()
    {
        parent::__construct();
    }

    public function get_teacher_data($user_id)
    {
        return parent::get_data($user_id);
    }

    public function update_teacher_data($user_id,$data)
    {
        if(!is_array($data)||empty($data)) return false;
        return $this->update_data_array($user_id,$data);
    }

    public function update_teacher_download_default($user_id,$download_default,$download_type='paper')
    {
        if(!$download_default) return false;
        return $this->update_data($user_id,$download_default,$download_type.'_download_default');
    }

    public function update_teacher_gender($user_id,$gender)
    {
        if(!$gender) return false;
        return $this->update_data($user_id,$gender,'gender');
    }

    public function update_teacher_school_id($user_id,$school_id)
    {
        if(!$school_id) return false;
        return $this->update_data($user_id,$school_id,'school_id');
    }

    public function update_teacher_survey($user_id,$gender,$school_id)
    {
        if(!$gender||!$school_id) return false;
        return $this->update_data_array($user_id,array('gender'=>$gender,'school_id'=>$school_id));
    }

}
