<?php
require_once('paper_question_type_model.php');

class Homework_Question_Type_Model extends Paper_Question_Type_Model {

    function __construct()
    {
        parent::__construct();
		$this->_namespace="homework";
    	$this->_table="homework_question_type";
   		$this->_paper_id="paper_id";	
    }
	
    //添加题型
    public function add_question_type($paper_id,$question_type_id,$name,$note='注释')
    {
        $data = array(
            $this->_paper_id=>$paper_id,
            'note'=>$note,
            'is_delete'=>0,
            'name'=>$name,
            'question_order'=>'',
            'qtype_id'=>$question_type_id
        );
        $this->db->insert($this->_table,$data);
        return $this->db->insert_id();
    }

	//批量添加题型
    public function add_question_types($paper_id,$question_type_id_list)
    {
        $paper_question_types=array();
        foreach($question_type_id_list as $qtype)
        {
                $paper_question_types[] = array(
                        $this->_paper_id=>$paper_id,
                        'note'=>'注释',
                        'is_delete'=>0,
                        'name'=>$qtype->name,
                        'question_order'=>'',
                        'qtype_id'=>$qtype->id
                );
        }
        $this->db->insert_batch($this->_table,$paper_question_types);
        return $this->db->affected_rows();
    }

	//根据试卷id查找题型
    function get_paper_question_types($paper_id)
    {
		$this->db->select($this->_table.'.*,question_type.is_select_type');
		$this->db->join('question_type','question_type.id='.$this->_table.'.qtype_id','left');
        $this->db->where($this->_table.'.'.$this->_paper_id,$paper_id);
        $this->db->where($this->_table.'.is_delete',0);
        $this->db->order_by($this->_table.'.id',"asc");
        return $this->db->get($this->_table)->result();
    }

}

/* end of homework_question_type_model.php */
