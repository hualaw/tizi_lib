<?php

class Paper_Save_Log extends MY_Model {

	protected $_table="paper_save_log";
	protected $_paper_table="paper_testpaper";
	protected $_paper_question_table="paper_question";
	protected $_paper_id="testpaper_id";
	protected $_subject_table="subject";
	
    function __construct()
    {
        parent::__construct();
    }

    // 添加保存纪录
    function add_save_log_record($user_id,$logname,$paper_id,$recovery_id=0)
    {
    	$data = array(
            'user_id'=>$user_id,
            'save_time'=>date("Y-m-d H:i:s"),
            'logname'=>$logname,
            $this->_paper_id=>$paper_id,
            'is_delete'=>0,
            'question_count'=>0
        );
        $this->db->select('id');
        $this->db->where('is_delete',0);
        $this->db->where($this->_paper_id,$paper_id);
        $query=$this->db->get($this->_paper_question_table);
        $question_count=$query->num_rows();
        $data['question_count']=$question_count?$question_count:0;
    	if($recovery_id)
    	{
    		$this->db->where($this->_paper_id,$recovery_id);
    		$this->db->update($this->_table,$data);
			return $this->db->affected_rows();
    	}
        else
        {
        	$this->db->insert($this->_table,$data);
			return $this->db->insert_id();
        }
    }

    // 删除存档记录
    function delete_save_log($paper_log_id,$user_id)
    {
		$this->db->where('user_id',$user_id);
		$this->db->set('is_delete',1);
        $this->db->where('id',$paper_log_id);
        $this->db->update($this->_table);
		return $this->db->affected_rows();
    }

	function get_save_log($save_log_id,$user_id)
	{
		$this->db->select('*, '.$this->_paper_id.' as paper_id');
		$this->db->where('user_id',$user_id);
		$this->db->where('id',$save_log_id);
		$query=$this->db->get($this->_table);
		return $query->row();
	}
	
	function get_save_log_by_paper_id($paper_id,$user_id)
	{
		$this->db->where('user_id',$user_id);
		$this->db->where($this->_paper_id,$paper_id);
		$query=$this->db->get($this->_table);
		return $query->row();
	}

    // 查询存档记录 
    function get_save_logs($user_id,$page_num=1,$where_type=0,$total=false,$subject_id=0)
    {
		$this->db->join($this->_paper_table,"{$this->_table}.{$this->_paper_id}={$this->_paper_table}.id",'left');
		$this->db->join($this->_subject_table,"{$this->_subject_table}.id={$this->_paper_table}.subject_id",'left');
		//$this->db->where("{$this->_paper_table}.subject_id",$subject_id);
	
		$this->db->select("{$this->_table}.*,{$this->_subject_table}.name as subject_name");
        $this->db->where("{$this->_table}.is_delete",0);
		$this->db->where("{$this->_table}.user_id",$user_id);
        $this->db->order_by("{$this->_table}.save_time","desc");

		if($where_type)
		{
			switch($where_type)
			{
				case 1:	$this->db->where("{$this->_table}.save_time >=",date("Y-m-d")." 00:00:00");
						break;
				case 2:	$yesterday=date("Y-m-d",strtotime("yesterday"));	
						$this->db->where("{$this->_table}.save_time >=",$yesterday." 00:00:00");
						$this->db->where("{$this->_table}.save_time <=",$yesterday." 23:59:59");
						break;
				case 3:	$week=date("Y-m-d",strtotime("this week"));	
						$this->db->where("{$this->_table}.save_time >=",$week." 00:00:00");	
						break;
				case 4: $this->db->where("{$this->_table}.save_time >=",date("Y-m")."-1 00:00:00");
                        break;
				default:break;
			}
		}

		if($total)
		{
			$query=$this->db->get($this->_table);	
			return $query->num_rows();
		}

		$limit=Constant::QUESTION_PER_PAGE;
        if($page_num<=0) $page_num=1;
        $offset=($page_num-1)*$limit;
        $this->db->limit($limit,$offset);
        return $this->db->get($this->_table)->result();
    }

    //布置试卷后 次数加一
    function incr_assign_count($save_log_id){
        $sql = "update {$this->_table} set assign_count = assign_count+1 where id = $save_log_id ";
        // echo $sql;die;
        $this->db->query($sql);
    }
}

/* end of paper_save_log.php */
