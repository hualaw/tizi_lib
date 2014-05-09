<?php

class Parent_Data_Model extends LI_Model {

    private $_table='user_parent_data';

    function __construct()
    {
        parent::__construct();
    }

    public function get_parent_data($user_id)
    {
        $this->db->where('user_id',$user_id);
        $query=$this->db->get($this->_table);
        return $query->row();
    }

    public function update_parent_gender($user_id,$gender)
    {
        if(!$gender) return false;
        return $this->update_parent_data($user_id,$gender,'gender');
    }

    public function update_parent_age($user_id,$age)
    {
        if($age < 0) return false;
        return $this->update_parent_data($user_id,$age,'age');
    }

    public function update_parent_birthday($user_id,$birthday)
    {
        if(!$birthday) return false;
        return $this->update_parent_data($user_id,$birthday,'birthday');
    }

    public function update_parent_phone($user_id,$phone)
    {
        if(!$phone) return false;
        return $this->update_parent_data($user_id,$phone,'bind_phone');
    }

    private function update_parent_data($user_id,$data_value,$data_name)
    {
        if(empty($data_name)) return false;

        $parent_data=$this->get_parent_data($user_id);

        if(empty($parent_data))
        {
            $this->db->insert($this->_table,array('user_id'=>$user_id,$data_name=>$data_value));
            if($this->db->affected_rows()) return $this->db->insert_id();
        }
        else
        {
            if($parent_data->{$data_name}===$data_value) return true;
            
            $this->db->where('user_id',$user_id);
            $this->db->update($this->_table,array($data_name=>$data_value));
            if($this->db->affected_rows()) return true;
        }
        return false;
    }

}
