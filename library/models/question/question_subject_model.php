<?php

class Question_Subject_Model extends LI_Model {
    
    private $_table = 'subject';
    private $_type_table = 'subject_type';
    private $_grade_table = 'grade';

    function __construct()
    {
        parent::__construct();
    }

    /* 得到所选科目的名字 */
    function get_subject_name($subject_id)
    {
        $this->db->select('name');
	    $this->db->where('id',$subject_id);
        $query = $this->db->get('subject');
        if($query->num_rows()==1) return $query->row()->name;
        else return false;
    }

    public function get_subjects(){
        return $this->db->query("select a.`id`,b.`id` as type,b.`name` from `subject` as a left join `subject_type` as b on a.`type`=b.`id` where a.`online`=1")->result();
    }


    function get_subject_by_type($subject_type,$grade_id=0,$grade_type=0)
    {
        $this->db->select($this->_table.'.*');
        if($grade_id)
        {
            $this->db->join($this->_grade_table,$this->_grade_table.'.grade_type='.$this->_table.'.grade','left');
            $this->db->where($this->_grade_table.'.id',$grade_id);
        }
        else if($grade_type)
        {
            $this->db->where($this->_table.'.grade',$grade_type);
        }
        if($subject_type) $this->db->where($this->_table.'.type',$subject_type);
        $query=$this->db->get($this->_table);
        return $query->result();
    }

    function get_all_subject($check_type='all')
    {
        $this->db->select('subject.id as sid, subject.name as sname, subject.*, subject_type.*');
        $this->db->where('online',1);
        $this->db->join('subject_type','subject_type.id=subject.type','left');
        $subjects=$this->db->get('subject')->result();
        if($check_type!='all')
        {
            foreach($subjects as $k=>$s)
            {
                if(!$this->check_subject($s->sid,$check_type)) unset($subjects[$k]);
            }
        }
        return $subjects;
    }

    function get_subject_type_by_id($subject_id)
    {
        $this->db->select('type');
        $this->db->where('id',$subject_id);
        $query = $this->db->get('subject');
        if($query->num_rows()==1) return $query->row()->type;
        else return false;
    }

	/*check subject*/
    function check_subject($subject_id=0,$check_type='all')
    {
        $check_subject = false;
        switch($check_type)
        {
            case 'cloud_question':/*网盘知识点目录*/
            case 'paper_question': 
            case 'homework_question': if($subject_id > 0 && $subject_id <= 21 || $subject_id >= 24 && $subject_id <= 26) $check_subject = true;break;
            case 'cloud':/*网盘同步目录*/
            case 'paper': 
            case 'homework': if($subject_id > 0 && $subject_id <= 26) $check_subject = true;break;
            case 'lesson': if($subject_id > 0 && $subject_id <= 26 && $subject_id != 23) $check_subject = true;break;
            case 'binding': if($subject_id > 0 && $subject_id <= 27) $check_subject = true;break;
            case 'all': if($subject_id > 0 && $subject_id <= 27) $check_subject = true;
            default: break;
        }
        return $check_subject;
    }

	function check_subject_type($subject_type=0,$check_type='all')
    {
        $check_subject_type = false;
        switch($check_type)
        {
            case 'all': if($subject_type > 0 && $subject_type <= 12) $check_subject_type = true;
            default: break;
        }
        return $check_subject_type;
    }	

	function get_subject_type($return_object=false,$check_type='all')
	{
        if($check_type=='homework') $this->db->where('id <=',9);
        if($check_type=='class') $this->db->where('id !=', 10);
        if($check_type=='exam_paper') $this->db->where('id <=', 9);
		$stype=$this->db->get('subject_type')->result();
        if($return_object) return $stype;
		foreach($stype as $st)
		{
			$subject_type[$st->id]=$st->name;
		}
		return $subject_type;
	}
    
    /**
     * 通过科目类型id获取科目类型名
     */  
    public function get_subject_type_name($type_id){

        $this->db->select('name');
        $this->db->where('id',$type_id);
        $query  = $this->db->get('subject_type');
        $result = $query->row();
        return $result->name;
    } 

    public function get_subject_type_info($sid){
        $result = $this->db
            ->query("select b.`id`,b.`name` from `subject` as a left join `subject_type`  as b on a.`type` = b.`id` where a.`id` = {$sid}")
            ->row_array();
        return $result;
    }

    public function get_subjects_by_tid($type){
        $result = $this->db->query("select `id` from `subject` where `type` = $type")->result();
        $group = array();
        foreach($result as $val){
            $group[] = $val->id;
        }
        return $group;
    }
    /*根据学段获取学科*/
    public function get_subject_by_grade($grade_id){
       return  $this->db->get_where('subject',array('grade'=>$grade_id,'online'=>1))->result();
    }
    /*根据学科获取学段*/
    function get_grade_by_subject($subject_id)
    {
        $this->db->select('name,grade');
        $this->db->where('id',$subject_id);
        $query = $this->db->get('subject');
        if($query->num_rows()==1) return $query->row();
        else return false;
    }
    /*根据学科获取学科年级下的所有学科*/
    function get_subjects_by_sid($subject_id)
    {
        $query = $this->db->query("SELECT * FROM subject WHERE grade=(SELECT grade FROM subject WHERE id=?) AND online=?",array($subject_id,1));
        return $query->result();
    }

}

/* end of subject.php */
