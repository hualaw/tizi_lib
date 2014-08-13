<?php
class Stu_Zuoye_Model extends LI_Model{
    private $_tab = 'zuoye_assign';
    private $_tab_stu = "zuoye_student";

    function __construct(){
        parent::__construct();
    }

    //获取某个学生的最新一条zuoye记录
    function get_lastest_stu_zuoye($class_id,$student_id){
        $select = "za.subject_id, za.end_time as zuoye_end_time, za.start_time as zuoye_start_time, za.unit_ids,za.unit_game_ids,za.video_ids,za.paper_ids,  zs.* ";
        $sql = "SELECT  $select  from zuoye_assign za 
                left join zuoye_student zs on zs.zy_assign_id=za.id 
                where class_id={$class_id} and za.status=1 and zs.user_id={$student_id} order by assign_time desc limit 1 ";
        $res = $this->db->query($sql)->result_array();
        if(!$res){return null;}
        $this->lastest_info($res);
        return $res;
    }

    //istudent homepage使用，只要最新的一条未完成的记录，完成的话返回null
    function lastest_info(&$data){
        if($data[0]['is_complete'] == 2 ){
            return null;//本次作业的题目全部完成, 就当没有新作业
        }
        if($data[0]['is_complete']==1 and $data[0]['zuoye_end_time']<time()){
            $data[0]['is_over_due'] = true;
        }
        $this->load->model('homework/unit_model');
        $this->load->model('question/question_category_model');
        // $tmp = $this->unit_model->get_banben_stage_by_unit($data[0]['unit_ids']);
        $tmp = $bb = array();
        $_unit_ids = explode(',', $data[0]['unit_ids']);
        if($_unit_ids){
            foreach($_unit_ids as $_us=>$_u){
                $node = $this->question_category_model->get_node($_u);
                if(isset($node->name)){$tmp[$_u] = $node; }
                $_parent = '';
                if($_u){
                    $_parent = $this->question_category_model->get_single_path($_u,'*');
                }
                if($_parent){
                    foreach($_parent as $ps=>$p){
                        if($p->depth==1){
                            $bb[$p->id] = $p->name;
                        }
                    }
                }
            }
        }
        $data[0]['units'] = $tmp;
        $this->handle_zuoye_info($data);

        // $this->load->model('homework/zuoye_intro_model');
        // $data[0]['video_entities'] = $this->zuoye_intro_model->get_videos_ids($data[0]['video_ids']);
        // $data[0]['game_entities'] = $this->zuoye_intro_model->get_game_entites($data[0]['unit_game_ids']);
        // if($data[0]['game_entities']){
        //     foreach($data[0]['game_entities'] as $k=>&$val){
        //         $val['is_finish'] = 1;//每个作业有没有完成
        //     }
        // }
    }

    //某次作业的游戏、视频信息，完成情况 , 通用，可同时处理 数组条记录
    function handle_zuoye_info(&$data){
        $this->load->model('homework/student_zuoye_model');
        $this->load->model('homework/game_model');
        $this->load->model('question/question_category_model');
        $this->load->model('question/question_subject_model');
        $this->load->model('video/videos_model');
        $this->load->library('qiniu');
        $this->qiniu->change_bucket('fls_');

        $zuoye_status = array(
            0 => '未完成',
            1 => '部分完成',
            2 => '已完成'
        );
        foreach($data as $key=>$val){
            $val['subject_name'] = $this->question_subject_model->get_subject_name($val['subject_id']);
            $val['start_time'] = date("Y-m-d H:i", $val['start_time']);
            $val['end_time'] = date("Y-m-d H:i", $val['end_time']);
            $val['complete_status'] = $zuoye_status[$val['is_complete']];
            $val['score'] = '--';
            $zuoye_info = array();
            $videos = $games = $papers = array();
            if(!empty($val['zuoye_info'])){
                $zuoye_info  = json_decode($val['zuoye_info'], true);
            }

            $student_video = $student_game = array();
            if($val['is_complete'] != 2){
                //处理视频作业
                if(isset($val['video_ids']) && !empty($val['video_ids'])){
                    $video_ids = explode(',', $val['video_ids']);
                    if(isset($zuoye_info['video']) && !empty($zuoye_info['video'])){
                        $student_video = $zuoye_info['video'];
                    }
                    foreach($video_ids as $video_id){
                        $video = array();
                        $video_info = $this->videos_model->get_lesson_by_id($video_id);
                        if(!empty($student_video) && in_array($video_id, $student_video)){
                            $video['is_complete'] = 1;
                        }else{
                            $video['is_complete'] = 0;
                        }
                        $video['id'] = $video_id;
                        $video['thumb_uri'] = $this->qiniu->qiniu_public_link($video_info->thumb_uri);
                        $video['video_link'] = waijiao_url('video/detail/'.$video_info->id);
                        $videos[] = $video;
                    }
                }
                //处理游戏作业
                if(isset($val['unit_game_ids']) && !empty($val['unit_game_ids'])){
                    $unit_game_ids = json_decode($val['unit_game_ids'], true);
                    if(isset($zuoye_info['game']) && !empty($zuoye_info['game'])){
                        $student_game = $zuoye_info['game'];
                    }
                    foreach($unit_game_ids as $unit_game_ids_key => $unit_game){
                        $game = $this->game_model->get_game_info($unit_game['game_id']);
                        $game['game_index'] = $unit_game_ids_key+1;
                        if(!empty($student_game) && isset($student_game[$unit_game_ids_key]) !== false){
                            $game['is_complete'] = 1;
                        }else{
                            $game['is_complete'] = 0;
                        }
                        $games[] = $game;
                    }
                }
                //处理试卷作业
                if(isset($val['paper_ids']) and $val['paper_ids']){
                    $this->load->model('exercise_plan/student_homework_model');
                    $paper_ids = json_decode($val['paper_ids'],true);
                    foreach($paper_ids as $pas=>$p){//$val['user_id'] == student_id
                        $papers[] = $this->student_homework_model->get_student_homework($val['user_id'],$p['assignment_id']);
                        // var_dump($papers);
                    }
                }
            }
            $val['task_num'] = count($videos) + count($games);
            $val['task_num_completed'] = count($student_video) + count($student_game);
            $val['videos'] = $videos;
            $val['games'] = $games;
            $val['papers'] = $papers;
            $data[$key] = $val;
            //$unit_id = array_shift(json_decode($val['video_ids'], true));
        }
    }

    //给新进班级的学生 未截止的作业
    function new_zuoye_for_new_stu($user_id,$class_id){
        $this->load->model('homework/zuoye_intro_model');
        $ass_ids = $this->zuoye_intro_model->not_over_zuoye($class_id);
        if(!$ass_ids)return null;
        $param = array();
        foreach($ass_ids as $k=>$val){
            $param[] = array('zy_assign_id' => $val['id'],'user_id' => $user_id,'id'=>0);
        }
        $result = $this->db->insert_batch($this->_tab_stu,$param);
        return $result;
    }
    
     

}
 
