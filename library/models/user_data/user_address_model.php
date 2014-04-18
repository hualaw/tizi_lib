<?php

class User_Address_Model extends LI_Model {

    private $_table='user_address';

    function __construct()
    {
        parent::__construct();
    }

    public function get_address($user_id)
    {
        $this->db->where('user_id',$user_id);
        $this->db->where('is_delete',0);
        $this->db->order_by('id','asc');
        $query=$this->db->get($this->_table);
        return $query->result();
    }

    public function add_address($data)
    {
        if(empty($data)) return false;
        $query=$this->db->insert($this->_table,$data);
        return $this->db->insert_id();
    }

    public function update_address($address_id,$data)
    {
        if(empty($data)||empty($address_id)) return false;
        $this->db->where('id', $address_id);
        $this->db->where('user_id', $data['user_id']);
        $query=$this->db->update($this->_table,$data);
        return $this->db->affected_rows();
    }

    public function delete_address($address_id,$user_id)
    {
        if(empty($address_id)) return false;
        $this->db->where('id', $address_id);
        $this->db->where('user_id', $user_id);
        $this->db->set('is_delete',1);
        $query=$this->db->update($this->_table);
        return $this->db->affected_rows();
    }

}
