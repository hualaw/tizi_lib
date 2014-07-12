<?php
class Homework_Assign_Model extends LI_Model{
    // private $_table = 'homework_assign';
    private $_table = 'paper_assign';
    function __construct(){
        parent::__construct();
    }

    //判断作业是否属于某个老师, 【是才能 给评语】
    function is_hw_belong($teacher_id,$assignment_id){
        $sql = "select count(1) as cc from $this->_table where user_id=? and id=?";
        $arr = array($teacher_id,$assignment_id);
        $cc = $this->db->query($sql,$arr)->row(0)->cc;
        if($cc) return true;
        return false;
    }
    
    /**
     * @deprecated
     * 从homework  controller布置作业。这是最老的方法。
     * 布置作业到某个班
     * @param array $param
     * @param int $param['start_time']
     * @param int $param['deadline']
     * @param int $param['class_id']
     * @param int $param['paper_id']
     * @param int $param['get_answer_way']
     * @param int $param['count']
     * @param int $param['user_id']
     */
    function ori_homework_assign($param){
    	// $param['assign_time'] = time();
    	// $param['is_assigned'] = true;
    	// $param['online'] = isset($param['online'])?$param['online']:true;//默认是在线作业
	    // $param['is_other'] = false;//现在就是false,没有‘其他作业’//isset($param['is_other'])?$param['is_other']:false;

        // $sql = "select count(1) as count from homework_assign where paper_id = {$param['paper_id']} and class_id={$param['class_id']} ";
        // $count = $this->db->query($sql)->row(0)->count; //检测是否有布置过重复的作业
        // if(!$count){ 
        	if($this->db->insert($this->_table,$param)){
        		return  $this->db->insert_id();
        	}
        // }
    	
        return false; 
    }

    //将本老师本班所有的历史作业都更新成已经检查
    function set_history_is_checked($teacher_id,$class_id){
        $sql = "update homework_assign set is_checked=1 where user_id=$teacher_id and class_id=$class_id";
        $this->db->query($sql);
    }
    
    /**
     * 查询：查看已布置的作业页面，要展示在页面上的信息:
     * 作业名称，id(即student_homework中的assignment_id),班级id，作业id，online，截止时间，作业名称,布置时间
     */
    function get_assigned_homework_info($id=false,$user_id=false,$class_id=false, $page=1, $offset=9){
    	$sql = "select ha.name,ha.id,ha.class_id,ha.paper_id,ha.start_time,ha.deadline,ha.is_assigned,
				ha.online,ha.assign_time,ha.is_other, ha.description, ha.count as hw_q_count,ha.is_checked from homework_assign ha left join classes_teacher ct on  ct.teacher_id=ha.user_id LEFT  join classes on classes.id = ha.class_id where  ct.class_id=ha.class_id and classes.close_status=0 ";
    	if($id){
	    	$sql .= " and ha.id=$id ";
    	}else{
    		$sql.=" and ha.user_id=$user_id ";
    		if($class_id){
    			$sql.=" and ha.class_id=$class_id ";
    		}
    	}
        $page = ($page-1)*$offset;
        $sql .= "order by ha.id desc  limit $page, $offset  ";
		$query = $this->db->query($sql);//echo $this->db->last_query();
		return $query->result_array();
    }
    
    /**
     * 通过id查找布置的作业的paper_assign表中的信息
     * @param int $id
     */
    function get_assigned_homework_info_by_id($id){
    	$this->db->select('*');
        $this->db->where('id',$id);
        $res = $this->db->get($this->_table);
        $res = $res->result_array();
        return $res[0];
    }

    //获取，当前条件下的总数（用于分页）
    function get_assigned_homework_count($user_id, $class_id){
             $sql = "select  count(1) as num  from homework_assign ha , classes_teacher ct  where user_id = $user_id and ha.user_id = ct.teacher_id and ha.class_id=ct.class_id ";
             if($class_id){
                $sql .= " and ha.class_id = $class_id";
             }
             return $this->db->query($sql)->row(0)->num;
    }

    function get_subjectid_by_assignid($id){
        $sql = "select subject_id from homework_assign ha ,homework_paper hp where ha.id=$id and ha.paper_id = hp.id limit 1";
        $res = $this->db->query($sql)->row(0)->subject_id;
        return $res;
    }
	
	/**
	 * 通过分配作业id获取paper info
	 * @param   int    $assignment_id 分配作业id
	 * @renturn object paper info
	 */
	public function getPaperIdByAssign($assignment_id){
		$assignment_id = intval($assignment_id);
		$sql = "SELECT ha.paper_id,ha.name,ha.deadline,b.subject_id FROM `homework_assign` as ha left join `homework_paper` as b on b.id = ha.paper_id WHERE ha.id=" . $assignment_id;
		$query    = $this->db->query($sql);
		$paper = $query->row();

		if(!empty($paper)){
			return $paper;
		}
		
		return false;
	}


    // 修改is_checked字段，表示已经检查过
    function is_checked($id){
        $id = intval($id);
        if(!$id){
           return false; 
        }
        $this->db->set('is_checked',1);
        $this->db->where('id',$id);
        return $this->db->update('homework_assign');
    }


    // 获取老师的subject id
    function get_teacher_subject($user_id){
        return $this->db->query("select register_subject as subject_id from user where id = '$user_id' limit 1")->row(0)->subject_id;
    }

    // 获取作业的名字
    function get_paper_title($id){
        return $this->db->query("select name from homework_assign where id = '$id' limit 1")->row(0)->name;
    }

	
	/**
	 * 家长端获取一定时间范围内截止的指定学生作业
	 * @param  int   $uid 		  学生id
	 * @param  int   $start_time  开始时间
	 * @param  int   $end_time    结束时间
	 * @return array 学生作业数组
	 */
	public function getHomeworksByParent($uid, $start_time, $end_time){
	
		$sql = "SELECT a.s_answer,a.correct_num,a.question_num,a.expend_time,b.deadline FROM `student_homework` AS a inner join `homework_assign` AS b ON a.assignment_id = b.id where a.student_id=" . $uid . " and b.deadline>=" . $start_time . " and b.deadline<". $end_time ." and b.is_assigned=1 and b.online=1";
		$query = $this->db->query($sql);
		return $query->result_array();
			
	}

    // new student gets their homework before deadline
    public function get_hw_to_new_stu($uid, $class_id){
        $now = time();
        $sql = "select * from homework_assign where class_id=$class_id and deadline > $now ";
        $hws = $this->db->query($sql)->result_array();
        if(!$hws){ // 没有的话就return true
            return true;
        }
        $result = true;
        foreach($hws as $key=>$val){
            $assignment_id = $val['id'];
            $sql = "select count(1) as count from student_homework where student_id='$uid' and assignment_id = '{$val['id']}'";
            $count = $this->db->query($sql)->row(0)->count;
            if(!$count){
                if($assignment_id){
                    // $online_status = false;
                    // if($paper_val['online']){
                    //     $online_status = true;
                    // }
                    $this->load->model("exercise_plan/student_task_model",'stm');
                    $data = array();
                    $result = true;
                    $save_work_data = null;
                    $save_work_data = array('aid'=>$assignment_id,'deadline'=>$val['deadline'],'uid_list'=>'');
                    $data[] = array('assignment_id' => $assignment_id,
                        'student_id' => $uid);
                    $result = $this->stm->advance_save($data);
                } // end of if loop
            }//end of if count 
        } // end of foreach loop     
        // var_dump($result);die;
        return $result;
    }


    function _get_homework_model($paper_id)
    {
		$this->load->model('paper/homework_model');
        $this->load->model('paper/homework_question_type_model');
        $this->load->model('paper/homework_question_model');
        $this->load->model('question/question_model');
        $this->question_model->init('exercise');

        $paper = array();
		$paper['paper_config']=$this->homework_model->get_paper_by_id($paper_id);
		//get paper question type
        $question_type_list=$this->homework_question_type_model->get_paper_question_types($paper_id);
		$paper_question_type_id_list=array(1=>array(),2=>array());
        $paper['question_config']=array(1=>array(),2=>array());
        foreach($question_type_list as $qtl)
        {
			$paper['question_config'][$qtl->is_select_type][$qtl->id]=$qtl;	
			$paper_question_type_id_list[$qtl->is_select_type][]=$qtl->id;

			if($qtl->question_order)
            {
                if($qtl->question_order) $paper_question_order[$qtl->is_select_type][$qtl->id]=explode(",",$qtl->question_order);
                foreach($paper_question_order[$qtl->is_select_type][$qtl->id] as $key=>$paper_question_id)
                {
                    $paper_question_order[$qtl->is_select_type][$qtl->id][$paper_question_id]=null;
                    unset($paper_question_order[$qtl->is_select_type][$qtl->id][$key]);
                }
            }
        }

		// get paper question
        $paper_question_list=$this->homework_question_model->get_paper_questions($paper_id);
        $question_id_list=array();
		$paper_question_id_list=array();
		$paper_question_order_list=array(1=>array(),0=>array());
		$paper['question_total']=array(1=>0,0=>0);
        foreach($paper_question_list as $ql)
        {
            $question_id_list[]=$ql->question_id;
			$paper_question_id_list[]=$ql->id;
			if(!isset($paper_question_order_list[$ql->is_select_type][$ql->qtype_id])&&isset($paper_question_order[$ql->is_select_type][$ql->qtype_id])) $paper_question_order_list[$ql->is_select_type][$ql->qtype_id]=$paper_question_order[$ql->is_select_type][$ql->qtype_id];
			if(in_array($ql->qtype_id,$paper_question_type_id_list[$ql->is_select_type]))
			{
            	$paper_question_order_list[$ql->is_select_type][$ql->qtype_id][$ql->id]=$ql->question_id;
				$paper['question_total'][$ql->is_select_type]++;
			}
        }		

		foreach($paper_question_order_list as $paper_section_type=>$paper_question_type)
        {
            if($paper_question_type)
            {
                foreach($paper_question_type as $paper_question_type_id=>$paper_question)
                {
                    if(!in_array($paper_question_type_id,$paper_question_type_id_list[$paper_section_type]))
                    {
                        unset($paper_question_order_list[$paper_section_type][$paper_question_type_id]);
                    }
                    if($paper_question)
                    {
                        foreach($paper_question as $paper_question_id=>$question)
                        {
                            if(!in_array($paper_question_id,$paper_question_id_list))
                            {
                                unset($paper_question_order_list[$paper_section_type][$paper_question_type_id][$paper_question_id]);
                            }
                            else if($question==null)
                            {
                                unset($paper_question_order_list[$paper_section_type][$paper_question_type_id][$paper_question_id]);
                            }
                        }
                    }
                }
            }
        }

		$paper_question_order_list[2]=$paper_question_order_list[0];
		unset($paper_question_order_list[0]);	
        if(isset($paper['question_config'][0])){
            $paper['question_config'][2]=$paper['question_config'][0];
            unset($paper['question_config'][0]);	
        }
		$paper['question_total'][2]=$paper['question_total'][0];
		unset($paper['question_total'][0]);
		$paper['paper_question']=$paper_question_order_list;

        // get question
        $question_list=$this->question_model->get_question_by_ids($question_id_list);
        if($question_list)
        {
            foreach($question_list as $ql)
            {
                $paper['question'][$ql->id]=$ql;
				$this->load->helper('img_helper');
                $paper['question'][$ql->id]->body=path2img($ql->body);
				$paper['question'][$ql->id]->answer=path2img($ql->answer);
				$paper['question'][$ql->id]->analysis=path2img($ql->analysis);
            }
        }
        else
        {
            $paper['question']=null;
        }
		//echo '<pre>';
        //print_r($paper);
        //echo '</pre>';
		return $paper;
    }

}

/*end of homework_assign_model.php*/
