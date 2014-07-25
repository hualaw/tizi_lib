<?php
//首页显示 某次作业 的 游戏+视频 + 图片 
class Zuoye_Intro_Model extends LI_Model{
    private $_tab = 'zuoye_assign';
    private $_tab_stu = "zuoye_student";

    function __construct(){
        parent::__construct();
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
}
 
