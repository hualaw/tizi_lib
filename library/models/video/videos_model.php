<?php

class Videos_Model extends MY_Model {

    private $_table = 'fls_video_lesson';
    private $_table_resources = 'fls_lesson_resources';
    private $_tb_unit="common_unit";
    private $_tb_stage="common_stage";
    private $_tb_learn_log="fls_learn_log";
    private $_tb_my_word="fls_my_words";
    private $_tb_exercise_wrong="fls_exercise_wrong";
    public function __construct(){
        parent::__construct();
    }

    function get_lesson_by_id($lesson_id,$select='*',$not_preview=true,$need_cat=false){
        $this->db->select(" $select ");
        if($not_preview){
            $this->db->where('online',1);//如果是preview就不用考虑online字段
        }

        //2014-08-07 start 增加联表，获取category id
        if($need_cat){
            $this->db->join($this->_tb_unit, "{$this->_tb_unit}.id = {$this->_table}.unit_id", 'left');
        }
        //2014-08-07 end

        if(is_array($lesson_id)){
            $this->db->where_in("{$this->_table}.id",$lesson_id);
            $query=$this->db->get($this->_table); //  echo $this->db->last_query();die;
            return $query->result_array();
        }else{
            $this->db->where("{$this->_table}.id",$lesson_id);
            $query=$this->db->get($this->_table); //echo $this->db->last_query();die;
            return $query->row(0);
        }
    }

    /*视频信息，附带unit_name, stage_name */
    function get_video_info_with_unit($lesson_id){
        $sql = "select v.*,u.unit_name,u.unit_number,u.prefix,u.edition_id,s.semester,s.name as stage_name from {$this->_table} v left join {$this->_tb_unit} u on u.id=v.unit_id left join {$this->_tb_stage} s on s.id=u.stage_id where v.id=$lesson_id and v.online=1";
        $result = $this->db->query($sql)->result_array();
        return $result;
    }
    
    /*根据学段获取视频（不分页）*/
    public function get_video_by_stage($user_id,$stage_id,$edition_id)
    {
    	$query = $this->db->query("SELECT v.`id`,u.`unit_name` as name,u.`prefix`,u.`unit_number`,v.`unit_id`,v.`en_title`,v.`chs_title`,v.`thumb_uri`  
    		FROM {$this->_tb_unit} AS u LEFT JOIN {$this->_table} AS v ON u.`id`= v.unit_id 
    		WHERE u.`stage_id` = ? AND u.`status`=1 AND u.`edition_id` = ? AND v.`online` = ? ORDER BY u.`id` ASC, v.`order_list` desc",array($stage_id,$edition_id,1));
    	$lesson_list = $query->result();
    	return self::prase_video_info($user_id,$lesson_list,1);
    }

    public function get_relation_lesson($unit_id)
    {
        $this->db->select("id,en_title,chs_title");
        $this->db->order_by('date','desc');
        return $this->db->get_where($this->_table,array('unit_id'=>$unit_id,'online'=>1))->result();
    }

    public function get_lesson_by_unit($user_id,$unit_id,$parse=true)
    {
    	$this->db->select("{$this->_table}.id,en_title,chs_title,unit_id,thumb_uri,{$this->_tb_unit}.category_id");
        
        //2014-08-07 start 增加联表，获取category id
        $this->db->join($this->_tb_unit, "{$this->_tb_unit}.id = {$this->_table}.unit_id", 'left');
        //2014-08-07 end

    	$this->db->order_by($this->_table.'.order_list','desc');
    	$video_list = $this->db->get_where($this->_table,array('unit_id'=>$unit_id,'online'=>1))->result();
        if($parse){
    	   self::prase_video_info($user_id,$video_list,2);
        }
        return $video_list;
    }

    public function prase_video_info($user_id,&$lesson_list,$type)
    {
        $active_data = -1;
        if($user_id){
            $this->load->model('statistics/statistics_model');
            $active_data = $this->statistics_model->get_user_active_data($user_id);
        }

        switch ($type) {
            case 1:
                $return_arr = array();
                foreach ($lesson_list as $val) {
                    if(!array_key_exists($val->unit_id, $return_arr)){
                        $return_arr[$val->unit_id]['name']=$val->name;
                        $return_arr[$val->unit_id]['prefix']=$val->prefix;
                        $return_arr[$val->unit_id]['unit_number']=$val->unit_number;
                        $return_arr[$val->unit_id]['video_list'][]=array(
                            'id'=>$val->id,
                            'en_title'=>$val->en_title,
                            'chs_title'=>$val->chs_title,
                            'thumb_uri'=>qiniu_pub_link($val->thumb_uri.'?imageView/0/w/'.Constant::VIDEO_THUMB_IMG_WIDTH.'/h/'.Constant::VIDEO_THUMB_IMG_HEIGHT),
                            'video'=>self::get_video_status($active_data,$val->id,Constant::LEARN_TYPE_WATCH),
                            'question'=>self::get_video_status($active_data,$val->id,Constant::LEARN_TYPE_SYNC_QUESTION),
                            'read'=>self::get_video_status($active_data,$val->id,Constant::LEARN_TYPE_SYNC_READ));
                    }else{
                        array_push($return_arr[$val->unit_id]['video_list'],array(
                            'id'=>$val->id,
                            'en_title'=>$val->en_title,
                            'chs_title'=>$val->chs_title,
                            'thumb_uri'=>qiniu_pub_link($val->thumb_uri.'?imageView/0/w/'.Constant::VIDEO_THUMB_IMG_WIDTH.'/h/'.Constant::VIDEO_THUMB_IMG_HEIGHT),
                            'video'=>self::get_video_status($active_data,$val->id,Constant::LEARN_TYPE_WATCH),
                            'question'=>self::get_video_status($active_data,$val->id,Constant::LEARN_TYPE_SYNC_QUESTION),
                            'read'=>self::get_video_status($active_data,$val->id,Constant::LEARN_TYPE_SYNC_READ)));
                    }
                }
                return $return_arr;
                break;
            case 2:
                foreach ($lesson_list as &$video) {
                    $video->thumb_uri=qiniu_pub_link($video->thumb_uri.'?imageView/0/w/'.Constant::VIDEO_THUMB_IMG_WIDTH.'/h/'.Constant::VIDEO_THUMB_IMG_HEIGHT);
                    $video->video = self::get_video_status($active_data,$video->id,Constant::LEARN_TYPE_WATCH);
                    $video->question=self::get_video_status($active_data,$video->id,Constant::LEARN_TYPE_SYNC_QUESTION);
                    $video->read=self::get_video_status($active_data,$video->id,Constant::LEARN_TYPE_SYNC_READ);
                }
                break;
             default:
                break;   
            }

    }

    protected function get_video_status($active_data,$video_id,$type){
        if($active_data===false) return -1;
        else if(is_array($active_data) and empty($active_data)) return 0;
        else if($active_data==-1) return $active_data;
        else return array_key_exists('video_'.$video_id.'_'.$type, $active_data)?1:0;
    }

    function get_people_name($user_id){
        $user_id = intval(($user_id));
        if(!$user_id){
            return null;
        }
        $sql = "select name from user where id = $user_id";
        $res = $this->db->query($sql)->row(0);
        $n = isset($res->name)?$res->name:'';
        return $n;
    } 

    /*视频学习统计*/
    // public function video_learn_statistics($user_id)
    public function lesson_learn_statistics($user_id)
    {
        $video_query = $this->db->query("SELECT COUNT(`id`) AS total ,COUNT(DISTINCT `lesson_id`) AS video_total FROM {$this->_tb_learn_log} WHERE user_id={$user_id} AND learn_type=3");
        $return_val = $video_query->row();

        $word_query = $this->db->query("SELECT COUNT(`id`) AS total FROM {$this->_tb_my_word} WHERE user_id={$user_id} AND status =1");
        $return_val->word_total = $word_query->row()->total;
        return $return_val;
    }

    /*同步练习统计*/
    // public function video_exercise_statistics($user_id)
    public function lesson_exercise_statistics($user_id)
    {
        $query = $this->db->query("SELECT COUNT(DISTINCT `question_id`) AS total FROM {$this->_tb_exercise_wrong} WHERE user_id={$user_id} AND type=1");
        $return_val = $query->row();

        $wrong_query = $this->db->query("SELECT COUNT(DISTINCT `question_id`) AS total FROM {$this->_tb_exercise_wrong} WHERE user_id={$user_id} AND type=1 AND result=0");
        $return_val->wrong_total = $wrong_query->row()->total;
        return $return_val;
    }

    /*根据lesson_id 获取视频列表信息*/
    public function get_lesson_resource_list($lesson_id)
    {
        $this->db->order_by('order_list','desc');
        $this->db->order_by('id','ASC');
        return $this->db->get_where($this->_table_resources,array('lesson_id'=>$lesson_id,'online'=>1))->result();
    }

    /*从数据库中获取 本单元/本chapter学习了多少天*/
    function learn_days_num($user_id,$unit_ids=null){
        $sql = "SELECT count(DISTINCT(FROM_UNIXTIME(op_time,'%Y-%m-%d'))) as num from fls_learn_log where user_id=$user_id ";
        if($unit_ids){
            $sql.= " and unit_id IN (" . implode(", ", $unit_ids) . ") ";
        }
        $num = $this->db->query($sql)->row(0)->num;
        return $num;         
    }
}