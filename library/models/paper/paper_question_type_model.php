<?php

class Paper_Question_Type_Model extends MY_Model {

	protected $_namespace="paper";
    protected $_table="paper_question_type";
    protected $_paper_id="testpaper_id";

    function __construct()
    {
        parent::__construct();
    }

    //添加题型
    public function add_question_type($paper_id,$section_id,$question_type_id,$name,$note='注释')
    {
        $data = array(
			$this->_paper_id=>$paper_id,
            'section_id' =>$section_id,
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
    public function add_question_types($paper_id,$section_id,$question_type_id_list)
    {
		$paper_question_types=array();
		foreach($question_type_id_list as $qtype)
		{
				$paper_question_types[] = array(
						$this->_paper_id=>$paper_id,
						'section_id' =>$section_id,
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

    //删除题型
    public function delete_question_type($paper_id,$paper_question_type_id,$is_recycle=false)
    {
        $this->db->trans_start();

		$this->db->where($this->_paper_id,$paper_id);
        $this->db->where('id',$paper_question_type_id);
		$this->db->set('is_delete',1);
        $this->db->update($this->_table);
		$affected_rows=$this->db->affected_rows();	

		//同时删除题型下所有题目
		$this->load->model('paper/'.$this->_namespace.'_question_model');
		$errorcode=$this->{$this->_namespace.'_question_model'}->delete_questions_by_paper_question_type($paper_id,$paper_question_type_id,false,$is_recycle);
        $this->db->trans_complete();
		
        if($this->db->trans_status()===false)
        {
			log_message('error_tizi','17041:Paper question type delete failed',array('uid'=>$user_id,'qpt_id'=>$paper_question_type_id));
            return false;
        }
        else if($errorcode||$affected_rows)
        {
            return true;
        }
        else
        {
            return false;
        }	
    }

    //根据分卷查找题型
    public function get_section_question_types($paper_id,$paper_section_id)
    {
		$this->db->where($this->_paper_id,$paper_id);
        $this->db->where('section_id',$paper_section_id);
        $this->db->where('is_delete',0);
        return $this->db->get($this->_table)->result();
    }

    //根据试卷id查找题型
    function get_paper_question_types($paper_id)
    {
        $this->db->where($this->_paper_id,$paper_id);
        $this->db->where('is_delete',0);
		$this->db->order_by("id","asc");
        return $this->db->get($this->_table)->result();
    }

	function reset_paper_question_type($paper_id)
	{
		$this->db->where($this->_paper_id,$paper_id);
		$this->db->set('is_delete',0);
		$this->db->update($this->_table);
		return $this->db->affected_rows();	
	}

	function save_question_order($paper_id,$qtype_id,$order)
	{
		$this->db->where($this->_paper_id,$paper_id);
		$this->db->where('id',$qtype_id);
		$this->db->set('question_order',$order);
		$this->db->update($this->_table);
		$affected_row=$this->db->affected_rows();
		if($affected_row==1)
		{
			return true;
		}			
		else
		{
			$this->db->where($this->_paper_id,$paper_id);
			$this->db->where('id',$qtype_id);
			$this->db->where('question_order',$order);
			$query=$this->db->get($this->_table);
			if($query->num_rows()==1) return true;
			else return false;
		}	
		return false;
	}

}

/* end of paper_question_type_model.php */
