<?php

class Stu_Video_Model extends LI_Model {

	private $_table='student_video';

	public function __construct()
	{

	}

	public function get_stu_video($grade=1,$page_num=1,$total=false)
	{
		if($grade) $this->db->where('grade_id',$grade);
		$this->db->where('online',1);
		if($total){
			$query=$this->db->get($this->_table);
			return $query->num_rows();
		}

		$limit=Constant::STU_VIDEO_PER_PAGE;
		if($page_num<=0) $page_num=1;
        $offset=($page_num-1)*$limit;
        $this->db->limit($limit,$offset);
        $this->db->order_by('date,id','desc');
		$query=$this->db->get($this->_table);
		return $query->result();
	}

	public function get_stu_video_by_id($vid)
	{
		$this->db->where('id',$vid);
		$this->db->where('online',1);
		$query=$this->db->get($this->_table);
		return $query->row();
	}

	public function get_video_by_grade($grade)
	{
		$this->db->where('grade_id',$grade);
		$this->db->where('online',1);
		$this->db->order_by('date,id','desc');
		$query=$this->db->get($this->_table);
		return $query->result();
	}

}