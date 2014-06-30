<?php

class Paper_Section_Model extends MY_Model {

	private $_table="paper_section";	
	private $_paper_id="testpaper_id";

    function __construct()
    {
        parent::__construct();
    }

    // 根据试卷id得到section
    function get_sections_by_paper($test_paper_id)
    {
        if ($test_paper_id)
        {
            $this->db->where($this->_paper_id, $test_paper_id);
			$this->db->order_by('type','asc');	
            return $this->db->get($this->_table)->result();
        }
    }
	
	function get_section_by_type($testpaper_id,$type=1)
	{
		if($testpaper_id)
		{
			$this->db->where($this->_paper_id,$testpaper_id);
			$this->db->where('type',$type);
			return $this->db->get($this->_table)->row()->id;
		}
		else
		{
			return false;
		}
	}

	function save_question_type_order($test_paper_id,$section_id,$order)
    {
        $this->db->where($this->_paper_id,$test_paper_id);
        $this->db->where('id',$section_id);
        $this->db->set('question_type_order',$order);
        $this->db->update($this->_table);
        return $this->db->affected_rows();
    }
}

/* end of paper_section_model.php */
