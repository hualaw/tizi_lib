<?php

class User_Statistics_Model extends MY_Model {

    private $_table='user_statistics';

    function __construct()
    {
        parent::__construct();
        $this->load->model("redis/redis_model");
    }

    public function get_statistics_data()
    {
        $statistics=array();
        if($this->redis_model->connect('statistics'))
        {
            $statistics=$this->cache->redis->hmget('user_statistics',array('teacher','student','parent','school','lesson_total','question_total'));
        }
        if(empty($statistics))
        {
            $this->db->order_by('id desc');
            $this->db->limit(1);
            $query=$this->db->get($this->_table);
            $statistics=$query->row_array();
        }
        return $statistics;
    }

    public function get_teacher_statistics($user_id)
    {
        $this->load->model("class/classes");
        $class_static = $this->classes->class_static($user_id);
        $this->load->model('cloud/cloud_model');
        $statistics['share_total'] = $this->cloud_model->share_file_total( 0, $user_id);
        //debug
        $statistics['cloud_storage'] = $this->cloud_model->get_user_cloud_storage($user_id,true);
        $this->load->model('homework/exercise_plan_model');
        $statistics['hw_total'] = $this->exercise_plan_model->teacher_ex_total( $user_id );
        $statistics['file_total']=$this->cloud_model->teacher_file_total( $user_id );
        $statistics = array_merge($statistics, $class_static);

        $privilege=array('name'=>'','max_credit'=>'','total'=>'','percent'=>0);
        $this->load->library('credit');
        $privilege = $this->credit->userlevel_privilege($user_id);
        $privilege['userlevel']['percent']=round($privilege['userlevel']['total']*100/$privilege['userlevel']['max_credit'],0);
        $this->smarty->assign('userlevel',$privilege['userlevel']);
        //end userlevel

        //paper
        if($this->redis_model->connect('download'))
        {
            $this->_paper_month_key=date('Y-m').'_paper_'.$user_id;
            $this->_homework_month_key=date('Y-m').'_homework_'.$user_id;
            $statistics['paper']=$this->cache->get($this->_paper_month_key);
            $statistics['homework']=$this->cache->get($this->_homework_month_key);
        }
        if(!isset($statistics['paper'])||!$statistics['paper']) $statistics['paper']=0;
        if(!isset($statistics['homework'])||!$statistics['homework']) $statistics['homework']=0;
        $statistics['paper_limit']=$privilege['privilege']['paper_permonth']['value'];
        $statistics['homework_limit']=30000;	//作业下载4.0删除
        if($statistics['paper']>$statistics['paper_limit']) $statistics['paper']=$statistics['paper_limit'];
        if($statistics['homework']>$statistics['homework_limit']) $statistics['homework']=$statistics['homework_limit'];

        /*备课文档下载统计 begin*/
        //debug
        $statistics['lesson_month_down']=0;
        $this->load->model('lesson/document_download_model','doc_down');
        $statistics['lesson_month_down'] = $this->doc_down->get_lesson_down_statistics($user_id,'m');
        $statistics['lesson_down_limit'] = $privilege['privilege']['lesson_permonth']['value'];
        /*备课文档下载统计 end*/

        return $statistics;
    }

}
