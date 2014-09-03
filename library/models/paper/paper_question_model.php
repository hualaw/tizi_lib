<?php

class Paper_Question_Model extends MY_Model {
	
	protected $_namespace="paper";
	protected $_table="paper_question";
	protected $_paper_question_type_table="paper_question_type";
	protected $_question_table="question";
	protected $_question_type_table="question_type";
	protected $_question_type_subject_table="question_type_subject";
	protected $_paper_table="paper_testpaper";
	protected $_paper_id="testpaper_id";
	protected $_thrift_zujuan=false;

    public function __construct()
    {
        parent::__construct();
        $this->load->config('thrift');
        if($this->config->item('thrift_zujuan_active'))
        {
        	$this->_thrift_zujuan=true;
        }
        if($this->session->userdata('user_id') == 812805643) $this->_thrift_zujuan=true;
    }

    /*添加试题到试题栏*/
    public function add_question_to_paper($paper_id,$question_id,$question_origin=0,$category_id=0,$course_id=0,$use_thrift=true)
    {
    	if($this->_thrift_zujuan&&$use_thrift)
    	{
    		$this->load->library('thrift_zujuan');
    		$qadd = $this->thrift_zujuan->add_question($question_id,$paper_id,$question_origin,$category_id,$course_id); 
    		return ($qadd == 'success')?true:false;
    	}

    	switch ($question_origin) 
    	{
    		case Constant::QUESTION_ORIGIN_MYQUESTION:
    				$this->_question_table="teacher_question";
    				$this->db->where($this->_question_table.'.status', 0);
    				break;
    		default:$this->db->where($this->_question_table.'.online', 1);
    				break;
    	}
		//获取作业问题类型id
		$this->db->select($this->_paper_question_type_table.'.id,'.$this->_paper_question_type_table.'.is_delete');
		$this->db->join($this->_question_type_table,$this->_question_type_table.".id=".$this->_paper_question_type_table.".qtype_id","left");
		$this->db->join($this->_question_table,$this->_question_table.".qtype_id=".$this->_question_type_table.".id","left");
		$this->db->where($this->_question_table.'.id', $question_id);
    	$this->db->where($this->_paper_question_type_table.'.'.$this->_paper_id, $paper_id);
		$paper_question_type = $this->db->get($this->_paper_question_type_table)->row();	
		if(empty($paper_question_type))
		{
			//添加合法题型到试卷中
			$this->db->select($this->_question_type_table.'.*');
			$this->db->join($this->_question_type_subject_table,$this->_question_type_subject_table.'.question_type_id='.$this->_question_type_table.'.id',"left");
			$this->db->join($this->_paper_table,$this->_paper_table.'.subject_id='.$this->_question_type_subject_table.'.subject_id',"left");
			$this->db->join($this->_question_table,$this->_question_table.'.qtype_id='.$this->_question_type_subject_table.'.question_type_id',"left");
			$this->db->where($this->_paper_table.'.id', $paper_id);
			$this->db->where($this->_question_table.'.id', $question_id);
			$question_type_id = $this->db->get($this->_question_type_table)->row();
			if(empty($question_type_id))
			{
				return false;
			}
			else
			{
				$paper_question_type_id=0;
				
				$this->load->model('paper/paper_section_model');
				$paper_section=$this->paper_section_model->get_sections_by_paper($paper_id);
				$this->load->model('paper/paper_question_type_model');
				$paper_question_type_id=$this->paper_question_type_model->add_question_type($paper_id,$paper_section[$question_type_id->is_section_type]->id,$question_type_id->id,$question_type_id->name);

				if($paper_question_type_id)
				{
					$paper_question_type=new stdclass();
					$paper_question_type->id=$paper_question_type_id;
					$paper_question_type->is_delete=0;
				}
				else
				{
					return false;
				}
			}
		}

    	//如果加入的题目对应的题型在试卷中被删除，则把原题型重置为未删除
		if($paper_question_type->is_delete)
		{
			$this->db->where('id',$paper_question_type->id);
			$this->db->set('is_delete',0);
			$this->db->update($this->_paper_question_type_table);
		}

    	//如果添加的题目对应的题目在试卷中被删除，需要重置为未删除, 如果未删除，且题目已存在，则返回错误
    	$this->db->where($this->_paper_id, $paper_id);
    	$this->db->where('question_id', $question_id);
    	$this->db->where('question_origin', $question_origin);
		$query=$this->db->get($this->_table);
    	$if_exist_question=$query->row();

    	if ($query->num_rows()>1)
		{
			log_message('error_tizi','400001:Duplicate paper question',array('namespace'=>$this->_namespace,'question_id'=>$question_id,'paper_id',$paper_id));
			return false;
		}
		//试题曾经被添加过
		else if($query->num_rows()==1)
		{
			// 试题曾被删除，现已重置
			if($if_exist_question->is_delete)
    		{
				$this->db->where($this->_paper_id, $paper_id);
            	$this->db->where('question_id', $question_id);
            	$this->db->where('question_origin', $question_origin);
				$this->db->set('qtype_id',$paper_question_type->id);				
				$this->db->set('is_delete',0);
        		if($category_id) $this->db->set('category_id', $category_id);
        		if($course_id) $this->db->set('course_id', $course_id);
            	$this->db->update($this->_table);
            	if($this->db->affected_rows()==1) return $if_exist_question->id;	
				else return false;
    		}
			//试题已存在
			else
			{
				return $if_exist_question->id;
			}
   		}
		//试题不存在
		else
		{
			$this->db->set($this->_paper_id,$paper_id);
			$this->db->set('question_id',$question_id);
			$this->db->set('question_origin', $question_origin);
			$this->db->set('qtype_id',$paper_question_type->id);
			$this->db->set('is_delete',0);
        	if($category_id) $this->db->set('category_id', $category_id);
        	if($course_id) $this->db->set('course_id', $course_id);
			$this->db->insert($this->_table);			
			$insert_id = $this->db->insert_id();
			if($insert_id) return $insert_id;
			else return false;
		}
    }

    //从试卷中删除多个试题
    public function delete_question_from_paper($paper_id,$paper_question_id_list,$question_origin=0,$is_paper_question_id=false,$is_recycle=false,$use_thrift=true) 
	{
		if($this->_thrift_zujuan&&$use_thrift)
    	{
			$this->load->library('thrift_zujuan');
			$paper_question_id_list_array=is_array($paper_question_id_list)?$paper_question_id_list:array($paper_question_id_list);
	    	if($question_origin===false) $question_origin=-1;
	    	$qdel = $this->thrift_zujuan->del_question($paper_id,$paper_question_id_list_array,$question_origin,$is_paper_question_id);
	    	return ($qdel == 'success')?true:false;
	    }

		if($is_paper_question_id) $_question_id='id';
		else $_question_id='question_id';

		$this->db->trans_start();	
		
        if(is_array($paper_question_id_list))
		{
			if(!empty($paper_question_id_list)) $this->db->where_in($_question_id,$paper_question_id_list);
			else return false;
		}
		else
		{
			$this->db->where($_question_id,$paper_question_id_list);
		}	

		$this->db->where($this->_paper_id,$paper_id);
		//question_orgin false 用于全部删除 cart
		if($question_origin !== false) $this->db->where('question_origin', $question_origin);
		$this->db->set('is_delete',1);
        $this->db->update($this->_table);
		$affected_rows=$this->db->affected_rows();

        //向回收站中添加记录
        if ($is_recycle&&$is_paper_question_id&&is_array($paper_question_id_list))
        {
            //$this->add_recycle_records($paper_question_id_list,$paper_id);
        }
        $this->db->trans_complete();

        if ($this->db->trans_status()===false)
        {
			log_message('error_tizi','17042:Paper question delete failed',array('uid'=>$user_id,'pq_id'=>$paper_question_id_list));
			return false;
        }
		else
		{
        	return true;
		}
    }  	
 
	//根据试卷id查询试题
	function get_paper_questions($paper_id)
    {
        $this->db->select($this->_table.'.*,'.$this->_paper_question_type_table.'.name,'.$this->_paper_question_type_table.'.section_id');
        $this->db->join($this->_paper_question_type_table, $this->_paper_question_type_table.'.id='.$this->_table.'.qtype_id',"left");
        $this->db->where($this->_table.'.'.$this->_paper_id, $paper_id);
        $this->db->where($this->_table.'.is_delete',0);
        $this->db->order_by($this->_table.'.qtype_id,'.$this->_table.'.id','asc');
        return $this->db->get($this->_table)->result();
    }

    //根据题型查询试题
    public function get_paper_questions_by_paper_question_type($paper_id,$paper_question_type_id)
    {
		$this->db->join($this->_question_table,$this->_question_table.".id=".$this->_table.".question_id","left");
		$this->db->where($this->_table.'.'.$this->_paper_id,$paper_id);
        $this->db->where($this->_table.'.qtype_id',$paper_question_type_id);
        $this->db->where($this->_table.'.is_delete',0);
        return $this->db->get($this->_table)->result();
   }

    /* 清空某一题型所对应的试题 */
    public function delete_questions_by_paper_question_type($paper_id,$paper_question_type_id,$erase_all=false,$is_recycle=false)
    {
        $this->db->trans_start();
		
		//根据题型id获取问题列表
		$this->db->where($this->_paper_id,$paper_id);
        if(!$erase_all) $this->db->where('qtype_id',$paper_question_type_id);
        $this->db->where('is_delete',0);
        $paper_questions=$this->db->get($this->_table)->result();
		
		$paper_question_id_list = array();
        foreach ($paper_questions as $paper_question)
        {
            $paper_question_id_list[]=$paper_question->question_id;
        }
		//删除列表中的问题
        $errorcode=$this->delete_question_from_paper($paper_id,$paper_question_id_list,false,false,$is_recycle);

        $this->db->trans_complete();

        if($this->db->trans_status()===false)
        {
			log_message('error_tizi','17043:Paper question delete by type failed',array('uid'=>$user_id,'pqt_id'=>$paper_question_type_id));
			return false;
        }
		else if($errorcode)
		{
			return true;
		}
		else
		{
			return false;
		}
    }
	
	public function change_paper_question_type($paper_id,$paper_question_id,$paper_question_type_id)
	{
		$this->db->where('id',$paper_question_type_id);
		$this->db->where($this->_paper_id,$paper_id);
		$query=$this->db->get($this->_paper_question_type_table);
		if($query->num_rows()==1)
		{
			$this->db->where('id',$paper_question_id);
			$this->db->where($this->_paper_id,$paper_id);
			$this->db->set('qtype_id',$paper_question_type_id);		
			$this->db->update($this->_table);
			if($this->db->affected_rows()==1) 
			{
				return true;
			}
			else 
			{
				$this->db->where($this->_paper_id,$paper_id);
				$this->db->where('id',$paper_question_id);
				$this->db->where('qtype_id',$paper_question_type_id);
				$query=$this->db->get($this->_table);
				if($query->num_rows()==1) return true;
				else return false;
			}
		}
		else
		{
			return false;
		}
	}

	function count_questions($paper_id){
        $sql = "SELECT count(1) as num FROM {$this->_table} WHERE {$this->_paper_id}=$paper_id AND is_delete=0 LIMIT 1";
        $query = $this->db->query($sql);
        $row = $query->row(0);
        return $row->num;
    }

    function get_question_ids($paper_id){
        $res =  $this->db->query("select question_id from {$this->_table} where {$this->_paper_id} = $paper_id order by id");
        $data = array();
        foreach($res->result_array() as $row){
            $data[] = $row['question_id'];
        }
        return $data;
    }

}

/* end of paper_question_model.php */
