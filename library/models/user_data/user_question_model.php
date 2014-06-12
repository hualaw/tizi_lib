<?php

class User_Question_Model extends MY_Model {

	public $_table="teacher_question";
    //public $_course_table="teacher_question_course";
	//private $_category_table="teacher_question_category";

    public function __construct()
    {
        parent::__construct();
    }


    /*get question list*/
    public function get_question_list($group_id,$page_num,$question_type,$question_level,$subject_id=0,$total=false)
    {
        $uid = $this->session->userdata('user_id');
        $this->db->where($this->_table.'.subject_id',$subject_id);
        $this->db->where($this->_table.'.user_id',$uid);
        $this->db->where($this->_table.'.status',0);
		if ($question_type) $this->db->where($this->_table.'.qtype_id',$question_type);
        if ($question_level) $this->db->where($this->_table.'.level_id',$question_level);
        
        if($group_id !== FALSE)
        {   
            $this->db->where($this->_table.'.group_id',$group_id);
        }
        if($total)
        {
            $this->db->select('count('.$this->_table.'.id) as total');
            $query=$this->db->get($this->_table);
            $doc_count=isset($query->row()->total)?$query->row()->total:0;
            return $doc_count;
        }
        else
        {
            $this->db->select($this->_table.".*,question_type.name as qtype_name");
            $this->db->join('question_type','question_type.id='.$this->_table.'.qtype_id','left');
            $limit=Constant::QUESTION_PER_PAGE;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;
            $this->db->order_by($this->_table.".date","desc");
            $this->db->order_by($this->_table.".id","desc");
            $this->db->limit($limit,$offset);
            //print_r($this->db);die;
            $query=$this->db->get($this->_table);
            $doc_content=$query->result();
            return $doc_content;
        }       
    }
    
    /**
     * 老师上传试题组卷
     * @param  array $question_id_arr 试题id
     * @return arrayObject
     */
    public function get_question_by_ids($question_id_arr)
    {
        if(!is_array($question_id_arr) or empty($question_id_arr))
        {
            return false;
        }
        $this->db->where_in($this->_table.'.id',$question_id_arr);
        $this->db->where($this->_table.'.status',0);
        $this->db->join('question_type','question_type.id='.$this->_table.'.qtype_id','left');
        $this->db->select($this->_table.".*,question_type.name as qtype_name");
        return $this->db->get($this->_table)->result();
    } 

    
    /*添加试题*/
    public function insert_new_question($insert_data)
    {
        $query = $this->db->insert($this->_table,$insert_data);
        if($query)
            return $this->db->insert_id();
        else
            return false;
    }

    /*更新试题*/
    public function update_question($question_id,$user_id,$update_data)
    {
        $this->db->where('id',$question_id);
        $this->db->where('user_id',$user_id);
        $query = $this->db->update($this->_table,$update_data);
        return $query?true:false;
    }

    /*将已有分组更新为未选分组返回未选分组的count*/
    public function remove_no_group($group_id,$uid,$subject_id)
    {
        $this->db->where(array('group_id'=>$group_id,'user_id'=>$uid,'status'=>0,'subject_id'=>$subject_id));
        $status = $this->db->update($this->_table,array('group_id'=>0));
        if($status){
            $this->db->select('count(`id`) as num');
            return $this->db->get_where($this->_table,array('group_id'=>0,'user_id'=>$uid,'status'=>0,'subject_id'=>$subject_id))->row()->num;
        }
        else
        {
            return 0;
        }
    }

    public function get_single_question($question_id,$uid)
    {
        $this->db->where($this->_table.'.id',$question_id);
        $this->db->where($this->_table.'.user_id',$uid);
        $this->db->where($this->_table.'.status',0);
        $this->db->select($this->_table.".*,question_type.name as qtype_name");
        $this->db->join('question_type','question_type.id='.$this->_table.'.qtype_id','left');
        return $this->db->get($this->_table)->row();
    }

    public function get_question_category($question_id,$table='category'){
        $type = $table == 'category'?0:1;
        $this->db->select('*');
        $this->db->from("teacher_question_category as QC");
        $this->db->join("category as C", "QC.category_id = C.id", 'left');
        $this->db->where('QC.question_id', $question_id);
        $this->db->where('QC.type', $type);
        $query_set = $this->db->get();
        return $query_set->result();
    }

    public function get_last_modified_question($user_id,$sel_subject)
    {
        $this->db->where($this->_table.'.user_id',$user_id);
        $this->db->where($this->_table.'.subject_id',$sel_subject);
        $this->db->select("`teacher_question`.`id`,`teacher_question`.`qtype_id`,`teacher_question`.`title`,`teacher_question`.`level_id`,
            `teacher_question`.`group_id`,`teacher_question`.`subject_id`, `question_type`.`name` AS qtype_name");
        $this->db->join('question_type','question_type.id='.$this->_table.'.qtype_id','left');
        $this->db->order_by($this->_table.'.date','desc');
        $this->db->limit(1);
        return $this->db->get($this->_table)->row();
    }

    
}

/* end of user_question_model.php */
