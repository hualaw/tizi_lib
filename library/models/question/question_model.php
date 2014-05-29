<?php

class Question_Model extends MY_Model {

	public $table="question";
    public $category="category";
    public $category_table="question_category";
	private $_question_type_subject_table="question_type_subject";
    private $_redis=false;

    public function __construct()
    {
        parent::__construct();
    }

    public function init($table='question',$category='category')
    {
        $this->table=$table;
        $this->category=$category;
        $this->category_table=$table.'_'.$category;
    }

    /*get questions*/
    public function get_search_question($category_id_list,$page_num=1,$qtype=0,$qlevel=0,$subject_id=0,$total=false)
    {
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect('q_count'))   
        {
            $this->_redis=true;
        }
        $q_key = $this->table.'_'.$this->category.'_'.$subject_id.'_'.$qtype.'_'.$qlevel.'_'.md5(implode('_',$category_id_list));               
        $q_count=0;
        $q_content='';
        if($this->_redis)
        {
            if($total)
            {
                $q_count=$this->cache->get($q_key);
                if($q_count) return $q_count;
            }
            else
            {
                $q_content=$this->cache->get('question_'.$page_num.'_'.$q_key);
                $q_content=json_decode($q_content);
                if(!empty($q_content)) return $q_content;
            }
        }

        $this->db->join($this->category_table,$this->category_table.'.question_id='.$this->table.'.id','left');
        $this->db->where_in($this->category_table.'.'.$this->category.'_id',$category_id_list);
		if($subject_id)
		{
			$this->db->join($this->_question_type_subject_table,$this->_question_type_subject_table.".question_type_id=".$this->table.".qtype_id","left");
			$this->db->where($this->_question_type_subject_table.".subject_id",$subject_id);
		}
        if ($qtype) $this->db->where($this->table.'.qtype_id',$qtype);
        if ($qlevel) $this->db->where($this->table.'.level_id',$qlevel);
        //if($qtype==3) $this->db->where($this->table.'.asw <>','');
        //if(!$qtype) $this->db->where("(".$this->table.".asw <> '' and ".$this->table.".qtype_id = 3) or (".$this->table.".qtype_id <> 3)");
        $this->db->where($this->table.'.online',1);
        //$this->db->group_by($this->table.'.id');
        $this->db->group_by($this->category_table.'.question_id');
        
        if($total&&!$q_count)
        {
            //$this->db->select('count('.$this->category_table.'.question_id) as total');
            $this->db->select($this->table.'.id');
            $query=$this->db->get($this->table);
            //$q_count=isset($query->row()->total)?$query->row()->total:0;
            $q_count=$query->num_rows();
            if($this->_redis)
            {
                $this->cache->save($q_key,$q_count,Constant::REDIS_QCOUNT_TIMEOUT);
            }
            return $q_count;
        }
        else if(!$total&&!$q_content)
        {
            $this->db->select($this->table.".id,".$this->table.".*,".$this->category.".name");
            $this->db->join($this->category,$this->category_table.'.'.$this->category.'_id='.$this->category.'.id','left');
            $limit=Constant::QUESTION_PER_PAGE;
            if($page_num<=0) $page_num=1;
            $offset=($page_num-1)*$limit;
            //$this->db->order_by($this->table.".date","desc");
            $this->db->order_by($this->category_table.'.question_id','desc');
            $this->db->limit($limit,$offset);
            $query=$this->db->get($this->table);
            $q_content=$query->result();
            if($this->_redis)
            {
                $expire=Constant::REDIS_QCOUNT_TIMEOUT;
                $this->cache->save('question_'.$page_num.'_'.$q_key,json_encode($q_content),$expire);
            }
            return $q_content;
        }       
    }

    /*get question list*/
    public function get_question_by_ids($question_id_list)
    {
		if(is_array($question_id_list)&&!empty($question_id_list))
		{
     		$this->db->where_in('id',$question_id_list);
            $this->db->where('online',1);
			$query=$this->db->get($this->table);
        	return $query->result();
		}
		else
		{
			return false;
		}
    }

    public function get_exam_question_by_ids($question_id_list)
    {
        if(is_array($question_id_list)&&!empty($question_id_list))
        {
            $this->db->where_in('id',$question_id_list);
            $this->db->where("(online=1 OR online=100)");
            $query=$this->db->get($this->table);
            return $query->result();
        }
        else
        {
            return false;
        }
    }

    /*get question list*/
    public function get_question_by_ids_with_text($question_id_list)
    {
        if(is_array($question_id_list)&&!empty($question_id_list))
        {
            $this->db->select($this->table.'_text.body as body_text,'.$this->table.'_text.answer as answer_text,'.$this->table.'_text.analysis as analysis_text,'.$this->table.'.*');
            $this->db->where_in($this->table.'.id',$question_id_list);
            $this->db->where($this->table.'.online',1);
            $this->db->join($this->table.'_text',$this->table.'_text.id='.$this->table.'.id');
            $query=$this->db->get($this->table);
            return $query->result();
        }
        else
        {
            return false;
        }
    }

    /*set question offline*/
    public function set_question_offline($question_id,$name_space)
    {
        $this->db->trans_start();
        $update_data = array(
            'online'=>0,
            'last_modified'=>date('Y-m-d H:i:s', time())
            );
        $this->db->where($name_space.'.id',$question_id);
        $this->db->update($name_space,$update_data);
        $this->db->trans_complete();
        if (false === $this->db->trans_status()){
            log_message('Error', "offline question_id: $question_id failed!");
            return false;
        }
        return true;
    }

    /*question update online*/
    function set_question_online($question_id,$main_data,$text_data,$category_id_list,$course_id_list,$name_space)
    {
        $this->db->trans_start();
        $this->db->where($name_space.'.id',$question_id);
        $this->db->update($name_space,$main_data);
        $this->db->where($name_space.'.id',$question_id);
        $this->db->update($name_space.'_text',$text_data);
        $this->update_question_relation($question_id,$category_id_list,$course_id_list,$name_space);
        if (false === $this->db->trans_status()){
            log_message('Error', "online question_id: $question_id failed!");
            return false;
        }else{
            if($name_space == 'question'){
                $this->db->query("INSERT INTO html2png_task(`type`,`main_id`) VALUES (1,{$question_id}),(2,{$question_id}),(3,{$question_id})");
            }else{
                $this->db->query("INSERT INTO html2png_task(`type`,`main_id`) VALUES (4,{$question_id}),(5,{$question_id}),(6,{$question_id})");
            }
        }
        return true;
    }

    /*new question update online*/
    function set_new_question_online($main_data,$text_data,$category_id_list,$course_id_list,$name_space)
    {
        $question_id = '';
        $this->db->trans_start();
        $this->db->insert($name_space,$main_data);
        $question_id = $this->db->insert_id();
        $text_data['id'] = $question_id;
        $this->db->insert($name_space.'_text',$text_data);
        $this->new_question_relation($question_id,$category_id_list,$course_id_list,$name_space);
        if (false === $this->db->trans_status()){
            log_message('Error', "online question_id: $question_id failed!");
            return false;
        }else{
            if($name_space == 'question'){
                $this->db->query("INSERT INTO html2png_task(`type`,`main_id`,`source`) VALUES (1,{$question_id},1),(2,{$question_id},1),(3,{$question_id},1)");
            }else{
                $this->db->query("INSERT INTO html2png_task(`type`,`main_id`,`source`) VALUES (4,{$question_id},1),(5,{$question_id},1),(6,{$question_id},1)");
            }
        }
        return $question_id;
    }

    /*new question relation*/
    private function new_question_relation($question_id,$category_id_list,$course_id_list,$name_space)
    {
        $this->db->trans_start();
        if($name_space == 'question'){
            if($category_id_list){
                foreach ($category_id_list as $key => $value) {
                    $this->db->insert($name_space.'_category',array('question_id'=>$question_id,'category_id'=>$value['category_id']));
                }
            }
            if($course_id_list){
                foreach ($course_id_list as $key => $value) {
                    $this->db->insert($name_space.'_category',array('question_id'=>$question_id,'category_id'=>$value['course_id']));
                }
            }
        }
        if($name_space == 'exercise'){
            if($category_id_list){
                foreach ($category_id_list as $key => $value) {
                    $this->db->insert($name_space.'_category',array('question_id'=>$question_id,'category_id'=>$value['category_id']));
                }
            }
            if($course_id_list){
                foreach ($course_id_list as $key => $value) {
                    $this->db->insert($name_space.'_course',array('question_id'=>$question_id,'course_id'=>$value['course_id']));
                }
            }
        }
        if (false === $this->db->trans_status()){
            log_message('Error', "online question_id relation: $question_id failed!");
            return false;
        }
        return true;
    }
    /*update question relation*/
    private function update_question_relation($question_id,$category_id_list,$course_id_list,$name_space)
    {
        $this->db->trans_start();
        if($name_space == 'question'){
            $this->db->delete($name_space.'_category',array('question_id'=>$question_id));
            if($category_id_list){
                foreach ($category_id_list as $key => $value) {
                    $this->db->insert($name_space.'_category',array('question_id'=>$value['question_id'],'category_id'=>$value['category_id']));
                }
            }
            if($course_id_list){
                foreach ($course_id_list as $key => $value) {
                    $this->db->insert($name_space.'_category',array('question_id'=>$value['question_id'],'category_id'=>$value['course_id']));
                }
            }
        }
        if($name_space == 'exercise'){
            $this->db->delete($name_space.'_category',array('question_id'=>$question_id));
            $this->db->delete($name_space.'_course',array('question_id'=>$question_id));
            if($category_id_list){
                foreach ($category_id_list as $key => $value) {
                    $this->db->insert($name_space.'_category',array('question_id'=>$value['question_id'],'category_id'=>$value['category_id']));
                }
            }
            if($course_id_list){
                foreach ($course_id_list as $key => $value) {
                    $this->db->insert($name_space.'_course',array('question_id'=>$value['question_id'],'course_id'=>$value['course_id']));
                }
            }
        }
        if (false === $this->db->trans_status()){
            log_message('Error', "online question_id relation: $question_id failed!");
            return false;
        }
        return true;
    }
}

/* end of question_model.php */
