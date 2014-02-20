<?php
/*
 * @description
 *
 */
class Student_Task_Model extends MY_Model{


    public $uid;

    private $per_page_num ;//每页数量

    private $homework_data = array();

    function __construct(){
        parent::__construct();
        $this->per_page_num = Constant::STU_HOMEWORK_PER_PAGE;
    }


    public function getTaskNums(){
        $data = $this->db->query("select count(*) as num from `student_task` where `uid` = {$this->uid}")->row_array();
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
        
        $tasks = array();
        $offset = $this->per_page_num * ($page_num-1);
        $data = $this->db
            ->query("select * from `student_task` where `uid` = {$this->uid} and `is_delete` = 0 order by `date` desc limit {$offset},{$this->per_page_num}")
            ->result_array();

        foreach($data  as $val){
            if($val['task_type'] == 1){
                $homework = $this->student_homework_model->get_homework($this->uid, $val['index_value']);
                $tasks[] = $homework;
            }elseif($val['task_type'] == 2){
                $video = $this->db
                    ->query("select * from `student_video` where `id` = {$val['index_value']}")
                    ->row_array();
                $tasks[] = $video;
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
