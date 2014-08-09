<?php
/**
 * @description 作业统计
 */
class Student_Homeworkstats_Model extends MY_Model{

    private $_redis = false;

    public function __construct(){

        parent::__construct();

        if($this->student_homework_model->connect_redis()){
            $this->redis = $this->cache->redis;
        }
    }

    public function homework_question_stats($uid){

        $stats = array();
        $result = $this->db->query("select sum(b.`online_count`) as online_num,sum(b.`offline_count`) as offline_num,sum(a.`correct_num`) as correct_num,sum(a.`online_done_num`) as online_done_num, sum(a.`offline_done_num`) as offline_done_num, d.`type` as subject from `student_homework` as a left join `homework_assign` as b on a.`assignment_id` = b.`id` left join `homework_paper` as c on b.`paper_id` = c.`id` left join `subject` as d on c.`subject_id` = d.`id` left join `student_task` as e on a.`id` = e.`index_value` and e.`uid` = {$uid} where a.`student_id` = {$uid} and e.`is_delete` = 0 group by c.`subject_id`")
          ->result_array();
        foreach($result as $key=>$val){
            $stats[$val['subject']] = $val;
        }
        return $stats;
    }

    //答题量
    public function get_question_done($uid){
        $result = $this->db->query("select sum(a.`online_done_num`) as q_do_num, count(b.`id`) as homework_count from `student_homework` as a right join `student_task` as b on a.`id` = b.`index_value` where a.`student_id` = {$uid} and b.`is_delete` = 0 and b.`task_type` = 1")
            ->row_array();
        return $result;
    }

    



    //添加统计
    public function addHomeworkStatsToCache($key,$data){
        
        if($this->_redis){
            foreach($data as $field=>$val){
                $this->_redis->hincrby($key,$field,$val);
            }
            return true;
        }   
        return false;
    }

    public function getHomeworkStatsFromCache($key){

        $keys = $this->keys($key."*");
        $result = array();
        if($this->_redis){
            foreach($keys as $key){
                $result[] = $this->_redis->hgetall($key);
            }
        }

        return $result;
    }





}
