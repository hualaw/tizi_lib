<?php

class Researcher_Data_Model extends LI_Model {

    private $_table='user_researcher_data';

    function __construct()
    {
        parent::__construct();
    }

    public function get_researcher_data($user_id)
    {
        $this->db->where('user_id',$user_id);
        $query=$this->db->get($this->_table);
        return $query->row();
    }

    private function update_researcher_data($user_id,$data_value,$data_name)
    {
        if(empty($data_name)) return false;

        $researcher_data=$this->get_researcher_data($user_id);

        if(empty($researcher_data))
        {
            $this->db->insert($this->_table,array('user_id'=>$user_id,$data_name=>$data_value));
            if($this->db->affected_rows()) return $this->db->insert_id();
        }
        else
        {
            if($researcher_data->{$data_name}===$data_value) return true;
            
            $this->db->where('user_id',$user_id);
            $this->db->update($this->_table,array($data_name=>$data_value));
            if($this->db->affected_rows()) return true;
        }
        return false;
    }

	public function perfect($user_id, array $data){
		$res = $this->get_researcher_data($user_id);

        if(empty($res)){
			$data["user_id"] = $user_id;
			$this->db->insert($this->_table, $data);
		} else {
			$this->db->where("user_id", $user_id);
			$this->db->update($this->_table, $data);
		}
		return $this->db->affected_rows();
	}
	
	public function check_domain($domain_name){
		if (preg_match("|^tizi*|", $domain_name)){
			return true;
		}
		$res = $this->db->query("select id from user_researcher_data where domain_name=?", 
			array($domain_name))->result_array();
		return isset($res[0]["id"]) ? true : false;
	}
}
