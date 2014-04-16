<?php

class User_Address_Model extends LI_Model {

    private $_table='user_address';
    private $_address_limit=3;

    function __construct()
    {
        parent::__construct();
    }

    public function get_address($user_id)
    {
        $this->db->where('user_id',$user_id);
        $this->db->order_by('id','asc');
        $this->db->limit($this->_address_limit);
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
        $query=$this->db->update($this->_table,$data);
        return $this->db->affected_rows();
    }

}
