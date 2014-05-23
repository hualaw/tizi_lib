<?php
/*
 * @description
 *
 */
class Student_Task_Model extends LI_Model{

    public $uid;
    public $task_type;//任务类型
    public $task_type_except;

    private $per_page_num ;//每页数量
    private $homework_data = array();


    function __construct(){
        parent::__construct();
    }


    public function getTaskNums(){

        $fetch_ext = '';
        if($this->task_type){
            $fetch_ext = ' and `task_type` = '.$this->task_type;   
        }
        if($this->task_type_except){
            $fetch_ext = ' and `task_type` != '.$this->task_type_except; 
        }
        $data = $this->db->query("select count(*) as num from `student_task` where `uid` = {$this->uid} {$fetch_ext} and `is_delete` = 0 ")->row_array();
        return $data['num'];
    }


    //push homework
    public function pushTaskOnAddHomework($uids,$s_h_id){

        $date = time();
        if(is_array($uids)){
            $this->db->trans_start();
            foreach($uids as $uid){
                $this->db
                    ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$s_h_id},1,{$uid},{$date})");
            }
            $this->db->trans_complete();
            return $this->db->trans_status();      
        }else{
            return $this->db
                ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$s_h_id},1,{$uids},{$date})");
        
        }
         
    }

    //分享 文件
    public function pushTaskOnShare($uids,$file_id,$time=false){
        $date = $time?$time:time();
        if(is_array($uids)){
            $this->db->trans_start();
            foreach($uids as $uid){
                $this->db
                    ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$file_id},3,{$uid},{$date})");//类型3是分享
            }
            $this->db->trans_complete();
            return $this->db->trans_status();      
        }else{
            return $this->db
                ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$file_id},3,{$uids},{$date})");
        }
    }

    /**
     * @info 订阅
     * @task_type : 4 
     */
    public function  pushTaskOnSubscription($uids, $ids,$type){
    	if($type==2){
    		//发布新文章
    		$articles=array(array(
    			'id' => $ids,
    			'last_modified_time' => date('Y-m-d H:i:s'),
    			) );
    	}else{
    		//如果是订阅则推送该教研员的十五篇文章
    		$sql = "select id,last_modified_time from researcher_article 
    		  	    where researcher_id = $ids order by last_modified_time desc limit 15";
    		$articles = $this->db->query($sql)->result_array();
    		if(empty($articles))return -1;
    	}
        if(!is_array($uids)) $uids = array($uids);
        $this->db->trans_start();
        foreach($uids as $uid){
            foreach($articles as $news_id){
                $sql_ext = " where `uid` = {$uid} and `index_value` = {$news_id['id']}";
                $result = $this->db->query("select `is_delete` from `student_task`{$sql_ext}")
                    ->row_array();
                if(isset($result['is_delete'])){
                    if($result['is_delete']){
                        $this->db
                            ->query("update `student_task` set `is_delete` = 0{$sql_ext}");
                    }
                }else{
                	$date = strtotime($news_id['last_modified_time']);
                    $this->db
                        ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$news_id['id']},4,{$uid},{$date})");                
                }
            }
        }
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    //发布调查问卷
    public function pushTaskOnSurvey($uids, $id){
 
        $date = time();
        if(is_array($uids)){
            $this->db->trans_start();
            foreach($uids as $uid){
                $this->db
                    ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$id},5,{$uid},{$date})");
            }
            $this->db->trans_complete();
            return $this->db->trans_status();      
        }else{
            return $this->db
                ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$id},5,{$uids},{$date})");
        
        }
       
    }

    //学生刚加入班级的时候，获取班级以前的分享
    public function pushShareFirstAboard($uid,$class_id){
        $this->load->model('cloud/cloud_model');
        $share = $this->cloud_model->get_share_files_by_class(0,$class_id,1,999);
        if($share){
            foreach($share as $key=>$val){
                $this->pushTaskOnShare($uid,$val['share_id'],$val['create_time']);
            }
        }
    }

    //push video
    public function pushTaskOnRegister($uid){

        $this->load->model('login/register_model');
        $this->load->model('user_data/student_data_model');
        
        
        $user_info = $this->register_model->get_user_info($uid);
        $user_info = $user_info['user'];

        if(isset($user_info->register_grade) && $user_info->register_grade){
            $grade = $this->student_data_model->get_grade_video($user_info->register_grade);

            $video = $this->_getHistoryVideoByGrade($grade);
            if(!$video) return false;
            return $this->db
                ->query("insert into `student_task` (`index_value`,`task_type`,`uid`,`date`)value({$video['id']},2,{$uid},".strtotime($video['date']).")");

        }
    }
        
    /**
     * @任务列表
     */
    public function getTaskByPageNum($page_num){
        
        $this->load->model('homework/student_survey_model','ssm');
        $tasks = array();   
        $this->per_page_num = Constant::STU_HOMEWORK_PER_PAGE;
        $offset = $this->per_page_num * ($page_num-1);

        $fetch_ext = '';

        if($this->task_type){
            $fetch_ext = ' and `task_type` = '.$this->task_type;
        }
        if($this->task_type_except){
            $fetch_ext = ' and `task_type` != '.$this->task_type_except; 
        }
        $data = $this->db
            ->query("select * from `student_task` where `uid` = {$this->uid} and `is_delete` = 0 {$fetch_ext} order by `date` desc limit {$offset},{$this->per_page_num}")
            ->result_array();

        foreach($data  as $val){
            if($val['task_type'] == 1){
                $homework = $this->student_homework_model->get_homework($this->uid, $val['index_value']);
                $homework['task_type'] = 1;
                $tasks[] = $homework;
            }elseif($val['task_type'] == 2){
                $video = $this->db
                    ->query("select * from `student_video` where `id` = {$val['index_value']}")
                    ->row_array();
                $video['task_type'] = 2;
                $tasks[] = $video;
            }elseif($val['task_type'] == 3){//3是分享
                $share=$this->db->query("select f.*,s.*,f.is_del as file_is_del,s.is_del as share_is_del from cloud_share s left join cloud_user_file f on f.id=s.file_id where s.id={$val['index_value']}")->row_array();
                $share['task_type'] = 3;
                $tasks[] = $share;
            }elseif($val['task_type'] == 4){
                $sql = "select ra.id,ra.title,ra.content as content ,ra.attached_file,ra.last_modified_time,ur.organization,ur.domain_name
                    from researcher_article  ra
                    left JOIN user_researcher_data ur on ur.id=ra.researcher_id
                    where ra.id = {$val['index_value']} and  ra.`status` = 1  ORDER BY ra.last_modified_time desc ";
                $article = $this->db->query($sql)
                    ->row_array();
                $article['attached_file'] = json_decode($article['attached_file'],true);
                $article['content'] = sub_str(filter_var($article['content'], FILTER_SANITIZE_STRING), 0, 220); 
                $article['task_type'] = 4;
                $tasks[] = $article;
            }elseif($val['task_type'] == 5){
                $survey = $this->ssm->getData($val['index_value']);
                if(!empty($survey)){
                    $survey['task_type'] =  5;
                    $tasks[] = $survey;
                }
            }
        }
        return $tasks;
    }
    
    private function _getHistoryVideoByGrade($grade){
    
        $data = $this->db
            ->query("select `id`,`date`,`grade_id` from student_video where  `online` = 1 and `grade_id` = {$grade} order by `date` desc ")
            ->result_array();
        
        if(!empty($data) && isset($data[0])){
            return $data[0];
        }
        return false;
        
    }

    private function _getTodayVideo($date=''){
        if(!$date){
            $date = date("Y-m-d");
        }
        $res = mysql_query("select `id`,`date`,`grade_id` from student_video where `date` like '{$date}%' and `online` = 1");
        $videos = array();
        if(mysql_num_rows($res)){
            while($data = mysql_fetch_array($res)){
                $grade = 
                $videos[$data['grade_id']] = $data;
            }
        }
        return $videos;
    }

    /**
     * @param student_id 学号
     * @param assiment_id 作业id
     * @param question_num 问题数量
     * @param date 日期
     * @info 预先存储
     */
    public function advance_save($data){
        $this->db->trans_start();
        foreach($data as $workinfo){
            $this->db->insert('student_homework',$workinfo);
            $id = $this->db->insert_id();
            $this->pushTaskOnAddHomework($workinfo['student_id'],$id);
        }
        $this->db->trans_complete();
        if (!$this->db->trans_status())                                 
        {                                                                        
            return false;
        }
        return true;
    }
    


}
