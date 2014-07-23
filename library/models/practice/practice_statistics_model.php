<?php
/**
 * @author saeed
 * @date   2013-10-28
 * @description 专项练习 - 统计
 */
require_once(__DIR__."/practice_model.php");
class Practice_Statistics_Model extends Practice_Model{
	
	const HOMEPAGE_RAND_PRACTICE = "homepage_rand_practice";
    
    public function __construct(){
        parent::__construct();
    }

    /**
     * @info 答题统计
     */
    public function get_practice_statistics($uid,$p_c_id){
        $result = $this->db
            ->query("select *  from `practice_statistics` where `user_id` = {$uid} and `p_c_id` = {$p_c_id}")
            ->row_array();
        return $result;
    }
    
    //更新统计
    public function add_practice_statistics($uid,$p_c_id,$data){
        $result = $this->get_practice_statistics($uid,$p_c_id);
        $status = false;
        if(!empty($result)){
            $id = $result['id'];
            $ext_sql = '';
            $ext_group = '';
            foreach($data as $data_key=>$data_val){
                $ext_group[] = " $data_key = `{$data_key}` + {$data_val} ";
            }
            $ext_group[] = "`updated_time` = ".time();
            $ext_sql = implode(",",$ext_group);
            if(!empty($ext_sql)){
                $status = $this->db->query("update `practice_statistics` set {$ext_sql} where `id` = {$id}");
            }
        }else{
            $data['user_id'] = $uid;
            $data['p_c_id'] = $p_c_id;
            $data['updated_time'] = time();
            $status = $this->db->insert('practice_statistics',$data);
        }
        return $status;
    }
    //获取知识点统计
    public function get_knowledge_statistics($uid,$p_c_id,$category_id=''){
        $sql_ext = '';
        if(!empty($category_id)){
            $sql_ext = "and a.`category_id`={$category_id}";
        }
        $result = $this
            ->db
            ->query("select a.*,b.`name` from `practice_knowledge_statistics` as a left join `category` as b on a.`category_id` = b.`id` where a.`user_id` = {$uid}  and a.`p_c_id` = $p_c_id {$sql_ext}")
            ->result_array();
        return $result;
    }
    //添加知识点统计
    public function add_knowledge_statistics($uid,$p_c_id,$category_id,$status=true){
        $result = $this->get_knowledge_statistics($uid,$p_c_id,$category_id);
        if(!empty($result)){
            if($status){
                $exe_sta = $this->db->query("update `practice_knowledge_statistics` set `correct_num` = `correct_num`+1 , `total_num`=`total_num`+1 where `user_id`={$uid} and `p_c_id`={$p_c_id} and `category_id` = {$category_id}");
            }else{
                $exe_sta = $this->db->query("update `practice_knowledge_statistics` set `total_num`=`total_num`+1 where `user_id`={$uid} and `p_c_id`={$p_c_id} and `category_id` = {$category_id}");
            }
        }else{
            $data = array(
                'user_id'=>$uid,
                'p_c_id'=>$p_c_id,
                'total_num'=>1,
                'category_id'=>$category_id,
            );                    
            if($status){
                $data['correct_num'] = 1; 
            } 
            $exe_sta = $this->db->insert('practice_knowledge_statistics',$data);
        }
        return $exe_sta;
    }
    /**/
    public function get_record_by_ids($uid,$ids){
        $ids_str = implode(",",$ids);
        $result = $this->db
            ->query("select * from `practice_question_record` where `user_id` = {$uid} and `practice_id` in ({$ids_str})")
            ->result_array();
        return $result;
    }
    /**
     * @info 获取练习题记录
     * @param $uid 用户
     * @param $category_id 
     * @param $p_c_id 
     * @param $num 数量
     */
    public function get_practice_question_record($uid,$p_c_id,$category_id,$num){
        $result = $this->db
            ->query("select * from `practice_question_record` where `user_id`={$uid} and `p_c_id` = {$p_c_id} and `category_id` = {$category_id} order by `updated_time` desc limit 0,{$num}")
            ->result_array();
        return $result;
    }
    public function get_records_by_pc($uid,$p_c_id){
        $result = $this->db
            ->query("select * from `practice_question_record` where `user_id` = {$uid} and `p_c_id` = {$p_c_id}")
            ->result_array();
        return $result;
    }
    //删除错误题目
    public function delete_wrong_question($uid,$practice_id){
        $status = $this->db->query("update `practice_question_record` set `is_show` = 0 where `user_id` = {$uid} and `practice_id` = {$practice_id}");
        return $status;
    }
    //难度统计
    public function update_defficulty_statistics($user_id,$p_c_id,$data){
        $result = $this->db->query("select `id` from `practice_difficulty_statistics` where `user_id` = {$user_id} and `p_c_id` ={$p_c_id}")
            ->row_array();
        if(!empty($result)){
            $ext_sql = '';
            $ext = array();
            foreach($data as $key=>$data_val){
                $ext[] = " `{$key}` = `{$key}` + {$data_val}";
            }
            $ext_sql = implode(",",$ext);
            if(!empty($ext_sql)){
                if($this->db->query("update `practice_difficulty_statistics` set $ext_sql where `id`= {$result['id']}")){
                    return true;
                }
            }
            return false;
        }else{
            $data['user_id'] = $user_id;
            $data['p_c_id'] = $p_c_id;
            if($this->db->insert('practice_difficulty_statistics',$data)){
                return true;
            }
            
        }
        return false;
    }
    
    public function fetch_defficulty_statistics($user_id,$p_c_id){

        $result = $this
            ->db
            ->query("select * from `practice_difficulty_statistics` where `user_id` = {$user_id} and `p_c_id` = {$p_c_id}")
            ->row_array();
        return $result;
        
    }
    
    //获得今日已做题目
    public function get_question_done($uid,$p_c_id){
        $key = 'question_done_'.$p_c_id.'_'.$uid;
        $statistics_redis = $this->connect_redis('practice_statistics');
        $pid_group = $statistics_redis->smembers($key);
        return $pid_group;
    }

    public function category_question_num($categories,$p_c_id = ''){

        if(is_array($categories) && !empty($categories)){
            $cate_str = implode(",",$categories);

            $e_stats = $this->db->query("select b.`category_id`,count(*) as num from `practice` as a left join `exercise_category` as b on a.`question_id` = b.`question_id`  where a.`tn` = 1 and a.`question_type` = 2 and a.`p_c_id` = {$p_c_id} and a.`online` = 1 and a.`question_type` = 2 and b.`category_id` in ({$cate_str}) group by b.`category_id` ")
                ->result_array();

            $q_stats = $this->db->query("select b.`category_id`,count(*) as num from `practice` as a left join `question_category` as b on a.`question_id` = b.`question_id`  where a.`tn` = 2 and a.`p_c_id` = {$p_c_id} and b.`category_id` in ({$cate_str}) and a.`online` = 1 and a.`question_type` = 2 group by b.`category_id` ")->result_array();

            $stats = array();

            foreach($e_stats as $val){
                if(isset($stats[$val['category_id']])){
                    $stats[$val['category_id']] += $val['num']; 
                }else{
                    $stats[$val['category_id']] = $val['num']; 
                }
            }
            foreach($q_stats as $val){
                if(isset($stats[$val['category_id']])){
                    $stats[$val['category_id']] += $val['num']; 
                }else{
                    $stats[$val['category_id']] = $val['num']; 
                }
            }
            return $stats;
        }
    }

    public function getQuestionNumByPc($id){

        $stats = $this->db->query("select count(*) as num from `practice`  where  `question_type` = 2 and `p_c_id` = {$id} and `online` = 1")
            ->row_array();
        return $stats['num'];
    }

	public function update_participants_stats($p_c_id){
		
		$stats = $this->db
			->query("select * from `practice_participants_stats` where `p_c_id` = {$p_c_id}")
			->row_array();

		if(empty($stats)){
			$this->db->query("insert into `practice_participants_stats` (`p_c_id`, `user_num`) values({$p_c_id}, 1)");
		}else{
			$user_num = $stats['user_num']+1;
			$this->db->query("update `practice_participants_stats` set `user_num` = {$user_num} where `p_c_id` = {$p_c_id}");
		}
		
	}

	public function get_participants_stats($num = 10){
		
		return $this->db
			->query("select a.`p_c_id`, a.`user_num`, b.`p_c_name` ,c.`grade`, c.`p_c_type` from `practice_participants_stats` as a left join `practice_category` as b on a.`p_c_id` = b.`id` left join `practice_category_info` as c on b.`id` = c.`p_c_id` order by a.`user_num` desc limit 0, {$num}")
			->result_array();
		
	}

    public function participants_top($num =10){

        if(!$num) return array();
        $stats = $this->get_participants_stats($num);
        $stats = $this->practice_statistics_model->_construct_p_c_list($stats);
        return $stats;

    }

    public function pk_top_random($num = 2){
        
        $stats = $this->participants_top(20);       
        $keys = array_rand($stats, $num);
        $data = array();
        array_walk($keys, function($val)use($stats, &$data){ $data[] = $stats[$val];});

        return $data;

    }

    public function myHistory($uid, $num = 10){

        if(!$uid || !$num) return array();
        $stats = array();
        $redis = $this->connect_redis('practice_statistics');
        $key = 'participants_stats_'.$uid;
        $pids = $redis->lrange($key, 0, $num -1);
        if(!empty($pids)){
            foreach($pids as $p_c_id){
                $category = $this->get_category_info($p_c_id);
                $stats[] = $category;
            }
        }
        $stats = $this->_construct_p_c_list($stats);
        return $stats;

    }

    private function _construct_p_c_list($stats){

        $grade = array(
            1  => '小学综合', 2  => '一年级',
            3  => '二年级', 4  => '三年级',
            5  => '四年级', 6  => '五年级',
            7  => '六年级', 8  => '初中综合',
            9  => '初一', 10  => '初二',
            11 => '初三', 12 => '高中综合',
            13 => '高一', 14 => '高二',
            15 => '高三'
        );
        $urls = array(
            1=>tizi_url().'practice/training/',
            2=>tizi_url().'practice/game/',
        );
        $this->load->model('question/question_subject_model');
        foreach($stats as $key=>$val){

            $data = $this->get_sid_by_cid($val['p_c_id']);
            $sid = $data['sid'];
            $subjects = $this->question_subject_model->get_subject_type(true,'homework');
            $subject_name = $subjects[$sid-1]->name;
            $stats[$key]['subject_id'] = $sid;
            $stats[$key]['subject_name'] = $subject_name;
            $stats[$key]['grade'] = isset($grades[$val['grade']])?$grades[$val['grade']]:'';
            $stats[$key]['icon'] = 'image/student/special/'.($val['p_c_type'] != 1 ?
                'gameImg'.$val['p_c_type'] : "subject_{$sid}").'.png';
            $stats[$key]['url'] = ($val['p_c_type'] == 1 ? $urls[1]:$urls[2]).$val['p_c_id'];

        }
        return $stats;

    }

	public function homepage_practice(){
		$fields = "default";
		$this->load->model("redis/redis_model");
		$data = array();
    	if($this->redis_model->connect("statistics")){
			$data = $this->cache->hget(self::HOMEPAGE_RAND_PRACTICE, $fields);
			if ($data){
				$data = json_decode($data, true);
			}
		}
		if (!$data or $data["last_update"] != date("Y-m-d")){
			
			$data = $this->pk_top_random(2);
			
			$data["last_update"] = date("Y-m-d");
			if($this->redis_model->connect("statistics")){
                $this->cache->hset(self::HOMEPAGE_RAND_PRACTICE, $fields, json_encode($data));
            }
		}
		return $data;
	}

}
