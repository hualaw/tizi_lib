<?php

class Question_type_Model extends MY_Model {

	private $_select_type='is_select_type';	

    function __construct()
    {
        parent::__construct();
    }

    /* 根据科目名称找到对应的题型 */
    function get_subject_question_type($subject_id,$is_array=true,$select=true)
    {
		if(!$select) $this->_select_type='is_section_type';
        $this->db->select('question_type.id,question_type.name,question_type.'.$this->_select_type);
        $this->db->from('question_type');
        $this->db->join('question_type_subject', 'question_type_subject.question_type_id = question_type.id');
        $this->db->where('question_type_subject.subject_id', $subject_id);
        $qtypes=$this->db->get()->result();
		
		if($is_array)
		{
			$question_type=array(1=>array(),2=>array());
            foreach($qtypes as $qtype)
            {
                if($qtype->{$this->_select_type}) $question_type[1][]=$qtype;
                else $question_type[2][]=$qtype;
            }
            return $question_type;	
		}
		else
		{
			return $qtypes;
		}
    }

    //获取题型的名称
    function get_type_name_by_id($id){
        $id = intval($id);
        if(!$id)return '';
        $sql = "select name from question_type where id = $id";
        $res = $this->db->query($sql)->result_array();
        if($res){
            return $res[0]['name'];
        }
        return '';
    }
}

/* end of question_type.php */
