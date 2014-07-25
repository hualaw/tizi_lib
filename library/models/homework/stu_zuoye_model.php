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
        $res = $this->db->query($sql)->row_array();
        if(!$res){return null;}
        $this->lastest_zuoye_info($res);
        return $res;
    }

    function lastest_zuoye_info(&$data){
        if($data['is_complete'] == 2 ){
            return null;//本次作业的题目全部完成, 就当没有新作业
        }
        if($data['is_complete']==1 and $data['zuoye_end_time']<time()){
            $data['is_over_due'] = true;
        }
        $this->load->model('homework/zuoye_intro_model');
        $data['video_entities'] = $this->zuoye_intro_model->get_videos_ids($data['video_ids']);
        $data['game_entities'] = $this->zuoye_intro_model->get_game_entites($data['unit_game_ids']);
        if($data['game_entities']){
            foreach($data['game_entities'] as $k=>&$val){
                $val['is_finish'] = 1;//每个作业有没有完成
            }
        }
        $this->load->model('homework/unit_model');
        $tmp = $this->unit_model->get_banben_stage_by_unit($data['unit_ids']);
        $data['units'] = $tmp['units'];
        // var_dump($data);die;
    }
    
     

}
 
