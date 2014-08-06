<?php

class Paper_Model extends MY_Model {
	
	protected $_namespace="paper";
	protected $_table="paper_testpaper";
	protected $_paper_question_type_table="paper_question_type";
	protected $_paper_question_table="paper_question";
	protected $_paper_section_table="paper_section";
	protected $_paper_id="testpaper_id";
    protected $_paper_save_log_table="paper_save_log";

    public function __construct()
    {
        parent::__construct();
    }

    //初始化试卷记录
    public function init_paper_record($subject_id,$user_id=0,$is_saved=0,$is_locked=0)
    {
    	// 第一条为初始化记录
    	$mirror_record=Constant::testpaper_mirror();
        if ($user_id) $mirror_record->user_id=$user_id;
        $mirror_record->subject_id=$subject_id;
		$mirror_record->is_saved=$is_saved;
        $mirror_record->is_locked=$is_locked;
	
        $this->db->insert($this->_table,$mirror_record);
		$paper_insert_id=$this->db->insert_id();
		
		if($paper_insert_id)
		{
			$this->db->trans_start();
			$paper_id=$paper_insert_id;
	
			//添加分卷，前两条为初始化记录
			$section_list=array(1, 2);			
			$mirror_section=Constant::testpaper_section_mirror();
			//得到科目对应题型
			$this->load->model('question/question_type_model');
			$qtypes=$this->question_type_model->get_subject_question_type($subject_id,true,false);

			$this->load->model('paper/paper_question_type_model');

			foreach ($section_list as $section_id)
			{
				$mirror_section_record=$mirror_section[$section_id];
				$mirror_section_record->{$this->_paper_id}=$paper_id;
				$this->db->insert($this->_paper_section_table,$mirror_section_record);

				$section_insert_id=$this->db->insert_id();
				if(!empty($qtypes[$section_id])) $errorcode=$this->paper_question_type_model->add_question_types($paper_id,$section_insert_id,$qtypes[$section_id]);
			}
			$this->db->trans_complete();
		}
		else
		{
			return false;
		}

        if ($this->db->trans_status()===false)
        {
			log_message('error_tizi','17021:Paper init failed',array('uid'=>$user_id,'subject_id'=>$subject_id));
			return false;
        }
		else if(isset($errorcode)&&!$errorcode)
		{
			return false;
		}

		return $paper_id;
    }

    //保存试卷
    public function save_paper($paper_id,$user_id,$name='',$is_copy=false,$is_save_log=true,$lock_type=0,$save_as=false)
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
        else $logname=$mirror_record->main_title;
		$subject_id=$mirror_record->subject_id;
        $recovery_id=$mirror_record->is_recovery;

        $mirror_id=$mirror_record->id;
        unset($mirror_record->id);
        $mirror_record->is_saved=1;
        $mirror_record->is_locked=$lock_type;
        $mirror_record->is_recovery=0;
        $this->db->insert($this->_table,$mirror_record);

        // copy new testpaper id
        $copy_tid = $this->db->insert_id();

        //复制分卷数据
        $this->db->where($this->_paper_id,$mirror_id);
        $sections=$this->db->get($this->_paper_section_table)->result();

        foreach($sections as $section)
        {
            $section_id=$section->id;
			$question_type_order=$section->question_type_order;
            unset($section->id);
            $section->{$this->_paper_id}=$copy_tid;
            $this->db->insert($this->_paper_section_table,$section);
            $copy_secid=$this->db->insert_id();

            $this->db->where('section_id',$section_id);
            $qtypes=$this->db->get($this->_paper_question_type_table)->result();

            foreach ($qtypes as $qtype)
            {
                /* 复制题型数据 */
                $qtype_id=$qtype->id;
				$question_order=$qtype->question_order;
                $qtype->section_id=$copy_secid;
				$qtype->{$this->_paper_id}=$copy_tid;
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
					$pquestion->{$this->_paper_id}=$copy_tid;
                    unset($pquestion->id);
                    $this->db->insert($this->_paper_question_table,$pquestion);
					$copy_qid=$this->db->insert_id();
					if($question_order!=''&&strpos($question_order,$pquestion_id)!==false)
                	{
                    	$question_order=str_replace($pquestion_id,$copy_qid,$question_order);
                	}	
                }
				//echo $question_order;
				$this->db->where('id',$copy_qtid);
				$this->db->set('question_order',$question_order);
				$this->db->update($this->_paper_question_type_table);
            }
			//echo $question_type_order;
			$this->db->where('id',$copy_secid);
            $this->db->set('question_type_order',$question_type_order);
            $this->db->update($this->_paper_section_table);		
        }

        $save_log=true;
        $save_log_id=0;
		if(!$is_copy&&$is_save_log)
		{
        	/* 添加存档记录 */
        	//$this->load->model('paper/paper_save_log');
        	if($save_as) $recovery_id=0;

        	$save_log=$this->add_save_log_record($user_id,$logname,$copy_tid,$recovery_id);
        	if(!$recovery_id) $save_log_id=$save_log;
        	if($save_log) $save_log=$this->set_paper_is_recovery($paper_id,$copy_tid);
        	if($recovery_id&&$save_log) $save_log=$this->set_paper_is_delete($recovery_id);
        }

        $this->db->trans_complete();  // 事务结束

        if($this->db->trans_status()===false)
        {
			log_message('error_tizi','17022:Paper save failed',array('uid'=>$user_id,'paper_id'=>$paper_id));
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
            $this->db->update($this->_paper_save_log_table,$data);
            return $this->db->affected_rows();
        }
        else
        {
            $this->db->insert($this->_paper_save_log_table,$data);
            return $this->db->insert_id();
        }
    }

	public function recover_save_paper($recover_paper_id,$user_id)
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
			$paper_save=$this->save_paper($recover_paper_id,$user_id,'',true);
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
				log_message('error_tizi','17023:Paper recover failed',array('uid'=>$user_id,'paper_id'=>$recover_paper_id));
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

	//保存试卷配置信息
	public function save_paper_config($test_paper_id, $test_paper_config, $section_config_list, $question_type_config_list)
    {
        $this->db->trans_start();

        $this->db->where('id', $test_paper_id);
        $query = $this->db->update($this->_table, $test_paper_config);
		$testpaper_affected_row=$this->db->affected_rows();

        $this->db->where($this->_paper_id, $test_paper_id);
        $sections = $this->db->get($this->_paper_section_table)->result();	

        foreach ($sections as $section)
        {
            /* 更新分卷设置 */
            $this->db->where('id', $section->id);
			if(isset($section_config_list[$section->type])&&$section->id==$section_config_list[$section->type]['id'])
			{
				$this->db->update($this->_paper_section_table, $section_config_list[$section->type]);
			}	
			
            /* 更新题型设置 */
            $section_id = $section->id;
            $this->db->where($this->_paper_id, $test_paper_id);
            $this->db->where('section_id', $section_id);
            $this->db->where('is_delete', FALSE);
            $qtypes = $this->db->get($this->_paper_question_type_table)->result();

            foreach ($qtypes as $qtype)
            {
                $this->db->where('id', $qtype->id);
                if(isset($question_type_config_list[$qtype->id]))
                {
                    $this->db->update($this->_paper_question_type_table, $question_type_config_list[$qtype->id]);
                }
            }
        }

        $this->db->trans_complete();  // 事务结束

        if ($this->db->trans_status() === FALSE)
        {
			log_message('error_tizi','17024:Paper config save failed',array('uid'=>$user_id,'paper_id'=>$test_paper_id));
			$testpaper_return = false;
        }
		else $testpaper_return = true;
		return $testpaper_return;
    }

    public function get_unsaved_paper_id($subject_id,$user_id)
    {
        $this->db->select("id");
        $this->db->where('is_saved',0);
        $this->db->where('user_id',$user_id);
        $this->db->where('subject_id',$subject_id);
        $query=$this->db->get($this->_table);
		if($query->num_rows()==1)
		{
			return $query->row()->id;
		}
		else if($query->num_rows()>1)
		{
        	$this->db->where('is_saved',0);
        	$this->db->where('user_id',$user_id);
        	$this->db->where('subject_id',$subject_id);
			$this->db->set('is_saved',1);
			$this->db->update($this->_table);
			return false;
		}	
		else
		{
			return false;
		}
    }

    public function get_unsaved_paper_id_by_user_id($user_id)
    {
        $this->db->select("id,subject_id");
        $this->db->where('is_saved',0);
        $this->db->where('user_id',$user_id);
        $query=$this->db->get($this->_table);
        return $query->result();
    }

    public function update_paper_user_id($user_id,$paper_id)
    {
        $this->db->where('id',$paper_id);
        $this->db->where('is_saved',0);
		$this->db->where('user_id',0);
        $this->db->set('user_id',$user_id);
        $this->db->update($this->_table);
        $affected_row=$this->db->affected_rows();

        if($affected_row)
        {
        	$this->db->where('id <>',$paper_id);
        	$this->db->where('user_id',$user_id);
        	$this->db->where('is_saved',0);
        	$this->db->set('is_saved',1);
        	$this->db->set('is_locked',Constant::LOCK_TYPE_DELETE);
        	$this->db->update($this->_table);
        }

        return $affected_row;
    }

    public function check_paper_id($paper_id)
    {
        $this->db->where('id',$paper_id);
        $this->db->where('user_id',0);
		$this->db->where('is_saved',0);
        $query=$this->db->get($this->_table);
        if($query->num_rows()==1) return true;
        else return false;
    }

    //根据testpaper的id得到testpaper的信息
    public function get_paper_by_id($paper_id)
    {
        if($paper_id)
        {
            $this->db->where('id',$paper_id);
            return $this->db->get($this->_table)->row();
        }
		else
		{
			return false;
		}
    }

	public function set_paper_is_saved($paper_id,$user_id)
	{
		$this->db->where('id',$paper_id);
		$this->db->where('user_id',$user_id);
		$this->db->where('is_saved',0);
		$this->db->set('is_saved',1);
		$this->db->set('is_locked',Constant::LOCK_TYPE_DELETE);
		$this->db->update($this->_table);
		return $this->db->affected_rows();
	}

	public function set_paper_is_recovery($paper_id,$new_paper_id)
	{
		$this->db->where('id',$paper_id);
    	$this->db->set('is_recovery',$new_paper_id);
    	$this->db->update($this->_table);
    	return $this->db->affected_rows();
	}

	public function set_paper_is_delete($paper_id)
	{
    	$this->db->where('id',$paper_id);
    	$this->db->set('is_locked',Constant::LOCK_TYPE_DELETE);
    	$this->db->update($this->_table);
    	return $this->db->affected_rows();
	}

	//save paper style
	private function save_paper_style($paper_id,$paper_style)
	{
		$this->db->where('id',$paper_id);
		$this->db->set('style',$paper_style);
		$this->db->update($this->_table);
		if($this->db->affected_rows()==1) return true;
		else return false;
	}

	public function get_subject_id_by_paper_id($paper_id)	
	{
		$this->db->select('subject_id');
		$this->db->where('id',$paper_id);
		$query=$this->db->get($this->_table);
		if($query->num_rows()==1) return $query->row()->subject_id;
		else return false;
	}

	public function reset_paper_recovery($paper_id)
	{
		$this->db->where('id',$paper_id);
		$this->db->set('is_recovery',0);
		$this->db->update($this->_table);
		if($this->db->affected_rows()==1) return true;
		else return false;
	}

}

/* end of paper_model.php */
