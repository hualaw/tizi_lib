<?php
require_once("/../paper/paper_question_model.php");

class Homework_Question_Model extends Paper_Question_Model {
	
    public function __construct()
    {
        parent::__construct();
		$this->_namespace="homework";
    	$this->_table="homework_question";
    	$this->_paper_question_type_table="homework_question_type";
    	$this->_question_table="question";
    	$this->_question_type_table="question_type";
        $this->_question_type_subject_table="question_type_subject";
        $this->_paper_table="homework_paper";
    	$this->_paper_id="paper_id";	
    }

	//根据试卷id查询试题
    public function get_paper_questions($paper_id)
    {
        $this->db->select($this->_table.'.*,'.$this->_paper_question_type_table.'.name,'.$this->_question_type_table.'.is_select_type');
        $this->db->join($this->_paper_question_type_table, $this->_paper_question_type_table.'.id='.$this->_table.'.qtype_id',"left");
		$this->db->join($this->_question_type_table,$this->_question_type_table.'.id='.$this->_paper_question_type_table.'.qtype_id');
        $this->db->where($this->_table.'.'.$this->_paper_id, $paper_id);
        $this->db->where($this->_table.'.is_delete',0);
        $this->db->order_by($this->_table.'.qtype_id,'.$this->_table.'.id','asc');
        return $this->db->get($this->_table)->result();
    }

	//根据试卷id查询试题分类数量
    public function get_paper_questions_section($paper_id)
    {
        $this->db->select('count('.$this->_table.'.id) as count,'.$this->_question_type_table.'.is_select_type');
        $this->db->join($this->_paper_question_type_table, $this->_paper_question_type_table.'.id='.$this->_table.'.qtype_id',"left");
        $this->db->join($this->_question_type_table,$this->_question_type_table.'.id='.$this->_paper_question_type_table.'.qtype_id');
        $this->db->where($this->_table.'.'.$this->_paper_id, $paper_id);
        $this->db->where($this->_table.'.is_delete',0);
		$this->db->group_by($this->_question_type_table.'.is_select_type');
        $this->db->order_by($this->_table.'.qtype_id,'.$this->_table.'.id','asc');
        return $this->db->get($this->_table)->result();
    }

}

/* end of homework_paper_question_model.php */
