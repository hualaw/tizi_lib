<?php
require_once('paper_model.php');

class Homework_Model extends Paper_Model {

    public function __construct()
    {
        parent::__construct();
		$this->_namespace="homework";
    	$this->_table="homework_paper";
    	$this->_paper_question_type_table="homework_question_type";
		$this->_paper_question_table="homework_question";
    	$this->_paper_id="paper_id";
    }

    //初始化试卷记录
    public function init_paper_record($subject_id,$user_id=0)
    {
        // 第一条为初始化记录
        $mirror_record=Constant::homework_mirror();
        if($user_id) $mirror_record->user_id=$user_id;
        $mirror_record->subject_id=$subject_id;
		$mirror_record->is_saved=0;
	
        $this->db->insert($this->_table,$mirror_record);
		$paper_insert_id=$this->db->insert_id();
		
		if($paper_insert_id)
		{
			$this->db->trans_start();
			$paper_id=$paper_insert_id;
	
			//得到科目对应题型
			$this->load->model('question/question_type_model');
			$qtypes=$this->question_type_model->get_subject_question_type($subject_id);

			$this->load->model('paper/homework_question_type_model');

			//选择题
			if(!empty($qtypes[1])) $errorcode=$this->homework_question_type_model->add_question_types($paper_id,$qtypes[1]);
			//非选择题
			if(!empty($qtypes[2])) $errorcode=$this->homework_question_type_model->add_question_types($paper_id,$qtypes[2]);	

			$this->db->trans_complete();
		}
		else
		{
			return false;
		}

        if ($this->db->trans_status()===false)
        {
			log_message('error_tizi','17031:Homework init failed',array('uid'=>$user_id,'subject_id'=>$subject_id));
			return false;
        }
		else if(isset($errorcode)&&!$errorcode)
		{
			return false;
		}

		return $paper_id;
    }

    //保存作业
    function save_homework($paper_id,$user_id,$name='',$is_copy=false,$is_save_log=true,$lock_type=0,$save_as=false)
    {
		$this->db->trans_start();

        //先将当前未保存的记录复制一条
        $this->db->where('user_id',$user_id);
        $this->db->where('id',$paper_id);
        $this->db->where('is_saved',$is_copy);
        $mirror_record=$this->db->get($this->_table)->row();

		if(!$mirror_record) return false;
        //Note: remove logname arugment.
		if($name) $logname=$name;
        else $logname=$mirror_record->name;
		$subject_id=$mirror_record->subject_id;
        $recovery_id=$mirror_record->is_recovery;

        $mirror_id=$mirror_record->id;
		$question_type_order=$mirror_record->question_type_order;
        $paper_question_order=$mirror_record->question_order;
        unset($mirror_record->id);
        $mirror_record->is_saved=1;
        $mirror_record->is_locked=$lock_type;
        $mirror_record->is_recovery=0;
        $this->db->insert($this->_table,$mirror_record);

        // copy new testpaper id
        $copy_tid = $this->db->insert_id();

        $this->db->where($this->_paper_id,$paper_id);
        $qtypes=$this->db->get($this->_paper_question_type_table)->result();

        foreach ($qtypes as $qtype)
        {
            /* 复制题型数据 */
            $qtype_id = $qtype->id;
			$question_order=$qtype->question_order;	
            $qtype->paper_id=$copy_tid;
            unset($qtype->id);
            $this->db->insert($this->_paper_question_type_table,$qtype);
            $copy_qtid=$this->db->insert_id();
			if($question_type_order!=''&&strpos($question_type_order,$qtype_id)!==false)
            {
                $question_type_order=str_replace($qtype_id,$copy_qtid,$question_type_order);
            }
			$this->db->where($this->_paper_id,$paper_id);
            $this->db->where('qtype_id', $qtype_id);
            $pquestions=$this->db->get($this->_paper_question_table)->result();
			

            foreach($pquestions as $pquestion)
            {
                if($pquestion->is_delete==1) continue;
                /* 复制题目数据 */
                $pquestion_id=$pquestion->id;
                $pquestion->qtype_id=$copy_qtid;
                $pquestion->paper_id=$copy_tid;
                unset($pquestion->id);
                $this->db->insert($this->_paper_question_table,$pquestion);
				$copy_qid=$this->db->insert_id();
                if($question_order!=''&&strpos($question_order,$pquestion_id)!==false)
                {
                    $question_order=str_replace($pquestion_id,$copy_qid,$question_order);
                }
                if($paper_question_order!=''&&strpos($paper_question_order,$pquestion_id)!==false)
                {
                    $paper_question_order=str_replace($pquestion_id,$copy_qid,$paper_question_order);
                }
            }
			$this->db->where('id',$copy_qtid);
            $this->db->set('question_order',$question_order);
            $this->db->update($this->_paper_question_type_table);
        }
		$this->db->where('id',$copy_tid);
        $this->db->set('question_type_order',$question_type_order);
        $this->db->set('question_order',$paper_question_order);
        $this->db->update($this->_table);

        $save_log=true;
        $save_log_id=0;
		if(!$is_copy&&$is_save_log)
		{
	  		/* 添加存档记录 */
  			$this->load->model('paper/homework_save_log');
            if($save_as) $recovery_id=0;
  			$save_log=$this->homework_save_log->add_save_log_record($user_id,$logname,$copy_tid,$recovery_id);
            if(!$recovery_id) $save_log_id=$save_log;
            if($save_log) $save_log=$this->set_paper_is_recovery($paper_id,$copy_tid);
            if($recovery_id&&$save_log) $save_log=$this->set_paper_is_delete($recovery_id);
		}

  		$this->db->trans_complete();  // 事务结束

 	 	if($this->db->trans_status()===false)
  		{
			log_message('error_tizi','17032:Paper save failed',array('uid'=>$user_id,'paper_id'=>$paper_id));
			return false;
   		}
        else if(!$save_log)
        {
            return false;
        }
		else
		{
			return array('paper_id'=>$copy_tid,'save_log_id'=>$save_log_id);
		}
   	}	
	
	function recover_save_homework($recover_paper_id,$user_id)
	{
		$this->db->where('id',$recover_paper_id);
        $this->db->where('is_saved',1);
        $this->db->where('user_id',$user_id);
        $query=$this->db->get($this->_table);
        if($query->num_rows()==1)
        {
            $subject_id=$query->row()->subject_id;
            $paper_id=$this->get_unsaved_paper_id($subject_id,$user_id);

            $this->db->trans_start();
            $paper_save=$this->save_homework($recover_paper_id,$user_id,'',true);
            $new_paper_id=$paper_save['paper_id'];
            if($new_paper_id)
            {
                if($paper_id)
                {
                    $this->db->where('user_id',$user_id);
                    $this->db->where('subject_id',$subject_id);
                    $this->db->where('is_saved',0);
                    $this->db->set('is_saved',1);
                    $this->db->set('is_locked',Constant::LOCK_TYPE_DELETE);
                    $this->db->set('is_recovery',0);
                    $this->db->update($this->_table);
                    if($this->db->affected_rows())
                    {
                        $this->db->where('id',$new_paper_id);
                        $this->db->set('is_saved',0);
                        //添加编辑状态
                        $this->db->set('is_recovery',$recover_paper_id);
                        $this->db->update($this->_table);
                    }
                }
                else
                {
                    $this->db->where('id',$new_paper_id);
                    $this->db->set('is_saved',0);
                    //添加编辑状态
                    $this->db->set('is_recovery',$recover_paper_id);
                    $this->db->update($this->_table);
                }
            }
            $this->db->trans_complete();

            if($this->db->trans_status()===false)
            {
				log_message('error_tizi','17033:Paper recover failed',array('uid'=>$user_id,'paper_id'=>$recover_paper_id));
                $this->db->trans_rollback();
                return false;
            }
            else
            {
                return $new_paper_id;
            }
        }
        else
        {
            return false;
        }	
	}

	function save_question_type_order($paper_id,$order)
    {
        $this->db->where('id',$paper_id);
        $this->db->set('question_type_order',$order);
        $this->db->update($this->_table);
        return $this->db->affected_rows();
    }

    function save_question_order($paper_id,$order)
    {
        $this->db->where('id',$paper_id);
        $this->db->set('question_order',$order);
        $this->db->update($this->_table);
        $affected_row=$this->db->affected_rows();
        if($affected_row==1)
        {
            return true;
        }           
        else
        {
            $this->db->where('id',$paper_id);
            $this->db->where('question_order',$order);
            $query=$this->db->get($this->_table);
            if($query->num_rows()==1) return true;
            else return false;
        }   
        return false;
    }

}

/* end of homework_model.php */
