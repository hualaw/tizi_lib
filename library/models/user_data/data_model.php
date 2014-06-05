<?php

class Data_Model extends LI_Model {

    protected $_table='';
    protected $_user_id='user_id';

    function __construct()
    {
        parent::__construct();
    }

    protected function get_data($user_id)
    {
        $this->db->where($this->_user_id,$user_id);
        $query=$this->db->get($this->_table);
        return $query->row();
    }

    protected function update_data($user_id,$data_value,$data_name)
    {
        if(empty($data_name)) return false;

        $parent_data=$this->get_data($user_id);

        if(empty($parent_data))
        {
            $this->db->insert($this->_table,array($this->_user_id=>$user_id,$data_name=>$data_value));
            if($this->db->affected_rows()) return $this->db->insert_id();
        }
        else
        {
            if($parent_data->{$data_name}===$data_value) return true;
            
            $this->db->where($this->_user_id,$user_id);
            $this->db->update($this->_table,array($data_name=>$data_value));
            if($this->db->affected_rows()) return true;
        }
        return false;
    }

    protected function update_data_array($user_id,$data)
    {
        if(empty($data)||!is_array($data)) return false;

        $parent_data=$this->get_data($user_id);

        if(empty($parent_data))
        {
            $data[$this->_user_id]=$user_id;
            $this->db->insert($this->_table,$data);
            if($this->db->affected_rows()) return $this->db->insert_id();
        }
        else
        {
            $check=0;
            foreach($data as $k=>$v)
            {
                if($parent_data->{$k}!==(string)$v) $check++;
            }

            if($check==0) return true;

            $this->db->where($this->_user_id,$user_id);
            $this->db->update($this->_table,$data);
            if($this->db->affected_rows()) return true;
        }
        return false;
    }

}
