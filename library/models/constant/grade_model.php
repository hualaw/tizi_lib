<?php

class Grade_model extends LI_Model{
	
	private $_table = "grade";

	function __construct()
    {
        parent::__construct();
    }
	
    function get_grade($grade_type = 0, $return_array = true)
    {
    	if($grade_type) $this->db->where('grade_type',$grade_type);
        $this->db->where('online',1);
        $this->db->order_by('listorder,id','asc');
    	$query = $this->db->get($this->_table);
    	$result = $query->result();

        if($return_array)
        {
            $grade = array();
            foreach($result as $r)
            {
                if(!isset($grade[$r->grade_type][0])) $grade[$r->grade_type][0]='';
                if($r->grade_type_name) $grade[$r->grade_type][0]=$r->grade_type_name;
                $grade[$r->grade_type][$r->id]=$r->name;
            }
            $result = $grade;
        }

    	return $result;
    }

}