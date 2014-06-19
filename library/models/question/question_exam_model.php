<?php

class Question_Exam_Model extends MY_Model {
	
	const HOMEPAGE_PS_EXAM = "homepage_ps_exam";

	private $_table="exam";
    private $_level_table="exam_level";
    private $_type_table="exam_type";
    private $_area_table="classes_area";
    private $_subject_table="subject";
    private $_subject_type_table="subject_type";
    private $_redis=false;

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('teacher_data_helper');
    }

    public function get_area($return_array=false,$kb=false)
    {
        $this->db->select("id,name");
        $this->db->where("level <=",1);
        $this->db->where("id <",33);
        $query=$this->db->get($this->_area_table);
        $area=$query->result();
        if($kb)
        { 
            $area[]=(object)array('id'=>'kb1','name'=>"新课标1");
            $area[]=(object)array('id'=>'kb2','name'=>"新课标2");
        }
        if($return_array)
        {
            $area_array=array();
            foreach ($area as $k=>$a) {
                $area_array[$a->id]=$a->name;
            }
            $area=$area_array;
        }
        return $area;
    }

    public function get_exam_type($return_array=false,$return_all=false)
    {
        $examtype=array();
        $query=$this->db->get($this->_type_table);
        $exam_type=$examtype[0]=$query->result();
        if($return_array)
        {
            $etype_array=array();
            foreach ($exam_type as $k=>$etype) {
                $etype_array[$etype->id]=$etype->name;
            }
            $exam_type=$examtype[1]=$etype_array;
        }
        if($return_all) $exam_type=$examtype;
        return $exam_type;
    }

    public function get_exam_level($return_array=false)
    {
        $query=$this->db->get($this->_level_table);
        $exam_level=$query->result();
        if($return_array)
        {
            $elevel_array=array();
            foreach ($exam_level as $k=>$elevel) {
                $elevel_array[$elevel->id]=$elevel->name;
            }
            $exam_level=$elevel_array;
        }
        return $exam_level;
    }

    public function get_search_exam($page_num=1,$subject_type=0,$grade=0,$exam_type=0,$area=0,$subject_id=0,$total=false)
    {
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('q_count'))   
        {
            $this->_redis=true;
        }

        if($subject_id) $subject_type=0;
        $e_key = $this->_table.'_'.$subject_type.'_'.$grade.'_'.$exam_type.'_'.$area.'_'.$subject_id;
        $e_count=0;
        $e_content='';
        if($this->_redis)
        {
            if($total)
            {
                $e_count=$this->cache->get($e_key);
                if($e_count) return $e_count;
            }
            else
            {
                $e_content=$this->cache->get('exam_'.$page_num.'_'.$e_key);
                $e_content=json_decode($e_content);
                if(!empty($e_content)) return $e_content;
            }
        }

        if($subject_type)
        {
            $this->db->join($this->_subject_table,$this->_subject_table.'.id='.$this->_table.'.subject_id','left');
            $this->db->where($this->_subject_table.'.type',$subject_type);
        }
        else if($subject_id)
        {
            $this->db->where($this->_table.'.subject_id',$subject_id);
        }

        if($grade) $this->db->where($this->_table.'.grade_id',$grade);
        if($exam_type) $this->db->where($this->_table.'.exam_type_id',$exam_type);
        if($area) 
        {
            if($area=='kb1'||$area=='kb2')
            {
                $this->db->where_in($this->_table.'.province_id',Constant::get_exam_kb($area));
            }
            else
            {
                $this->db->where($this->_table.'.province_id',$area);
            }
        }

        $this->db->where($this->_table.'.online',1);
        //$this->db->group_by($this->_table.'.id');

        if($total&&!$e_count)
        {
            $this->db->select($this->_table.'.id');
            $query=$this->db->get($this->_table);
            $e_count=$query->num_rows();
            if($this->_redis)
            {
                $this->cache->save($e_key,$e_count,Constant::REDIS_QCOUNT_TIMEOUT);
            }
            return $e_count;
        }
        else if(!$total&&!$e_content)
        {
            $this->db->select($this->_table.'.*');
            $limit=Constant::QUESTION_PER_PAGE;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;

            $this->db->order_by($this->_table.'.date','desc');
            $this->db->limit($limit,$offset);
            $query=$this->db->get($this->_table);
            $e_content=$query->result();
            if($this->_redis)
            {
                $expire=Constant::REDIS_QCOUNT_TIMEOUT;
                $this->cache->save('exam_'.$page_num.'_'.$e_key,json_encode($e_content),$expire);
            }
            return $e_content;
        }
    }

    public function get_exam_by_id($exam_id)
    {
        $this->db->where('id',$exam_id);
        $this->db->where($this->_table.'.online',1);
        $query=$this->db->get($this->_table);
        return $query->row();
    }

	public function homepage_position($area_id, $subject_type = 1){
		$fields = "{$area_id}_{$subject_type}";
		$this->load->model("redis/redis_model");
        $data = array();
    	if($this->redis_model->connect("statistics")){
			$data = $this->cache->hget(self::HOMEPAGE_PS_EXAM, $fields);
			if ($data){
				$data = unserialize($data);
			}
		}
		if (!$data or $data["last_update"] != date("Y-m-d")){
			$data = array();
			$this->db->select("id,title,exam_type_id,grade_id");
			$this->db->where("province_id", $area_id);
			$this->db->where("(`subject_id` = ".$subject_type." OR `subject_id` = ".intval($subject_type+9).")");
			$this->db->where("online", "1");
			$this->db->order_by("year", "desc");
			$this->db->limit(6, 0);
			$res = $this->db->get($this->_table);
			$alldata = $res->result_array();
			if ($alldata){
				if (count($alldata) > 6){
					$rand = array_rand($alldata, 6);
					foreach ($rand as $value){
						$data["data"][] = $alldata[$value];
					}
				} else {
					$data["data"] = $alldata;
				}
			} else {
				$data["data"] = array();
			}
			$data["last_update"] = date("Y-m-d");
            if($this->redis_model->connect("statistics")){
                $this->cache->hset(self::HOMEPAGE_PS_EXAM, $fields, serialize($data));
            }
		}
    	return $data;
	}
}

/* end of question_exam_model.php */
