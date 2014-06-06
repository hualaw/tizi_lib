<?php
require_once('data_model.php');

class Parent_Data_Model extends Data_Model {

    protected $_table='user_parent_data';

    function __construct()
    {
        parent::__construct();
    }

    public function get_parent_data($user_id)
    {
        return parent::get_data($user_id);
    }

    public function update_parent_data($user_id,$data)
    {
        if(!is_array($data)||empty($data)) return false;
        return $this->update_data_array($user_id,$data);
    }

    public function update_parent_gender($user_id,$gender)
    {
        if(!$gender) return false;
        return $this->update_data($user_id,$gender,'gender');
    }

    public function update_parent_age($user_id,$age)
    {
        if($age < 0) return false;
        return $this->update_data($user_id,$age,'age');
    }

    public function update_parent_birthday($user_id,$birthday)
    {
        if(!$birthday) return false;
        return $this->update_data($user_id,$birthday,'birthday');
    }

    public function update_parent_bind_phone($user_id,$phone)
    {
        if(!$phone) return false;
        return $this->update_data($user_id,$phone,'bind_phone');
    }

    public function update_parent_detail($user_id,$gender,$birthday)
    {
        if(!$gender||!$birthday) return false;
        return $this->update_data_array($user_id,array('gender'=>$gender,'birthday'=>$birthday));
    }

    public function update_parent_child_school($user_id,$school_id,$grade_id)
    {
        if(!$school_id||!$grade_id) return false;
        return $this->update_data_array($user_id,array('child_school'=>$school_id,'child_grade'=>$grade_id));
    }

    public function update_parent_survey($user_id,$gender,$child_school,$child_grade)
    {
        if(!$gender||!$child_school||!$child_grade) return false;
        return $this->update_data_array($user_id,array('gender'=>$gender,'child_school'=>$child_school,'child_grade'=>$child_grade));
    }

}
