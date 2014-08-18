<?php
//首页显示 某次作业 的 游戏+视频 + 图片 
class Zuoye_Intro_Model extends LI_Model{
    private $_tab = 'zuoye_assign';
    private $_tab_stu = "zuoye_student";

    function __construct(){
        parent::__construct();
    }

    function get_paper_entites($zy){
        $papers = null;
        if(isset($zy['paper_ids']) and $zy['paper_ids']){
            $this->load->model('exercise_plan/homework_assign_model');
            $paper_ids = json_decode($zy['paper_ids'],true);
            foreach($paper_ids as $pas=>$p){
                $_pap = $this->homework_assign_model->get_assigned_homework_info_by_id($p['assignment_id']);       
                if(isset($p['package_id'])){
                    $_pap['package_id'] = $p['package_id'];
                }
                $papers [] = $_pap;
            }
        }
        // var_dump($papers);
        return $papers;
    }
    
    function get_game_entites($games){
        $games = json_decode($games,true);
        if(!$games){return null;}
        $this->load->model('homework/game_type_model');
        foreach($games as $k=>&$val){
            $tmp = $this->game_type_model->get_game_with_game_type($val['game_id']);
            $val['game_type_name'] = $tmp['type_name'];
        }
        return $games;
    }
     

    //拆分video_ids字段 
    function get_videos_ids($video_ids){
        if(!$video_ids){return null;}
        $v_arr = explode(',', $video_ids);
        $this->load->helper('array');
        $v_arr = explode_to_distinct_and_notempty($video_ids);
        if(!$v_arr){return null; }
        //现在不需要获取具体视频资源
        // $res = array();
        // $this->load->helper('qiniu');
        // qiniu_set_bucket('fls_');
        // foreach($v_arr as $k=>$val){
        //     $i = $this->videos_model->get_video_by_id($val);
        //     if($i){
        //         $i->video_uri = qiniu_pub_link($i->video_uri);
        //         $res[] = $i;
        //     }
        // }
        // qiniu_set_bucket();
        return $v_arr;
    }

    //未截止的作业
    function not_over_zuoye($class_id){
        $now = time();
        $sql = "select id from {$this->_tab} where end_time > $now and class_id = $class_id";
        $res = $this->db->query($sql)->result_array();
        return $res;
    }

    //老师某次作业，获得积分
    function change_assign_score($ass_id,$score){
        if($score>0){
            $sql = "update {$this->_tab} set score = score + $score where id = $ass_id and status = 1 ";
            $this->db->query($sql);
        }
    }
}
 
