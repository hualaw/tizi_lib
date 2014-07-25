<?php
class Stu_Zuoye_Model extends LI_Model{
    private $_tab = 'zuoye_assign';
    private $_tab_stu = "zuoye_student";

    function __construct(){
        parent::__construct();
    }

    //获取某个学生的最新一条zuoye记录
    function get_lastest_stu_zuoye($class_id,$student_id){
        $select = "za.end_time as zuoye_end_time, za.start_time as zuoye_start_time, za.unit_ids,za.unit_game_ids,za.video_ids,  zs.* ";
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
        $tmp = $this->unit_model->get_banben_stage_by_unit($data[0]['unit_ids']);
        $data[0]['units'] = $tmp['units'];
// var_dump($data);die;
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
        $this->load->model('video/videos_model');
        $this->load->library('qiniu');
        $this->qiniu->change_bucket('fls_');

        $zuoye_status = array(
            0 => '未完成',
            1 => '部分完成',
            2 => '已完成'
        );
        foreach($data as $key=>$val){
            $val['subject_name'] = '英语';
            $val['start_time'] = date("Y-m-d H:i", $val['start_time']);
            $val['end_time'] = date("Y-m-d H:i", $val['end_time']);
            $val['complete_status'] = $zuoye_status[$val['is_complete']];
            $val['score'] = '--';
            $zuoye_info = array();
            $videos = $games = array();
            if(!empty($val['zuoye_info'])){
                $zuoye_info  = json_decode($val['zuoye_info'], true);
            }

            $student_video = $student_game = array();
            if($val['is_complete'] != 2){
                if(isset($val['video_ids']) && !empty($val['video_ids'])){
                    $video_ids = explode(',', $val['video_ids']);
                    if(isset($zuoye_info['video']) && !empty($zuoye_info['video'])){
                        $student_video = $zuoye_info['video'];
                    }
                    foreach($video_ids as $video_id){
                        $video = array();
                        $video_info = $this->videos_model->get_video_by_id($video_id);
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
            }

            $val['task_num'] = count($videos) + count($games);
            $val['task_num_completed'] = count($student_video) + count($student_game);
            $val['videos'] = $videos;
            $val['games'] = $games;
            $data[$key] = $val;
            //$unit_id = array_shift(json_decode($val['video_ids'], true));
        }
    }
    
     

}
 
