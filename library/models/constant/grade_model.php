<?php

class Grade_model extends LI_Model{
	
	private $_table = "grade";

	function __construct()
    {
        parent::__construct();
    }
	
    function get_grade($grade_type = 0, $return_array=false)
    {
    	if($grade_type) $this->db->where('grade_type',$grade_type);
    	$query = $this->db->get($this->_table);
    	$result = $query->result();
    	return $result;
    }

}