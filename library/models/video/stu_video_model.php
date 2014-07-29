<?php

class Stu_Video_Model extends LI_Model {

	private $_table='student_video';
	public function __construct()
	{

	}

	//每日口语app端
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
        $this->db->order_by('date','desc');
		$query=$this->db->get($this->_table);
		return $query->result();
	}

	//每日口语web端
	public function get_stu_video_by_id($vid)
	{
		$this->db->where('id',$vid);
		$this->db->where('online',1);
		$query=$this->db->get($this->_table);
		return $query->row();
	}

	public function get_video_by_grade($grade=0)
	{
		if($grade) $this->db->where('grade_id',$grade);
		$this->db->where('online',1);
		$this->db->order_by('date','desc');
		$query=$this->db->get($this->_table);
		return $query->result();
	}

	public function get_video_by_date($grade=0,$from=false,$to=false)
	{
		if($grade) $this->db->where('grade_id',$grade);
		if($from && $to)
		{
			$this->db->where('date <=',$to);
			$this->db->where('date >=',$from);
		}
		$this->db->where('online',1);
		$this->db->order_by('date','desc');
		$query=$this->db->get($this->_table);
		return $query->result();
	}

	/** 更新每日口语信息
	 * @param $vid
	 * @param $data
	 * @return bool
	 */
	public function update_video($vid, $data){
		$this->db->trans_start();
		$this->db->update($this->_table, $data, 'id = ' . $vid);
		$this->db->trans_complete();

		if ($this->db->trans_status() === false) {
			return false;
		}
		return true;
	}

	public function get_videos_by_grade($grade=1,$page_num=1,$limit,$vid=false,$total=false)
	{
		if($grade) $this->db->where('grade_id',$grade);
		$this->db->where('online',1);
		if($total){
			$query=$this->db->get($this->_table);
			return $query->num_rows();
		}
		if(!$vid){
			if($page_num<=0) $page_num=1;
	        $offset=($page_num-1)*$limit;
	        $this->db->limit($limit,$offset);
		}else{
			$this->db->where('id <=',$vid);
			$this->db->limit($limit);
		}
        $this->db->order_by('date','desc');
		$query=$this->db->get($this->_table);
		return $query->result();
	}
}