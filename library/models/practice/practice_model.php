<?php
/**
 * @author saeed
 * @date   2013-10-16
 * @description 专项练习
 */
require_once(__DIR__."/practice_exception.php");
class Practice_Model extends MY_Model{

    public $q_c_mapping;//问题,知识点关联

	private $_redis=false;

    public $category_id;

    private $table_exercise_ids;

    private $table_question_ids;

    private $pro_categories;


    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * @info 获取所有分类
     */ 
    public function get_all_categories(){
        $result = $this->db->query("select * from `practice_category`")
            ->result_array();
        return $result;
    }
    /**
     * @info 获取某分类科目id
     */
    public function get_sid_by_cid($cid,$categories=false){
        if(!$categories){
            $result = $this->get_all_categories();
        }else{
            $result = $categories;
        }
        $level = 0;
        search:
        $level++;
        foreach($result as $result_val){
            if($result_val['id'] == $cid){
                if($result_val['pid'] != 0){
                    $cid = $result_val['pid'];
                    goto search;
                }
                return array('sid'=>$result_val['id'],'level'=>$level);
            }
        }
    }

    public function get_sids_by_cids($ids){
        $result = $this->get_all_categories();
        $data = array();
        foreach($ids as $id){
            $s_info = $this->get_sid_by_cid($id,$result);
            $s_info['p_c_id'] = $id;
            $data[] = $s_info;
        }
        return $data;
    }
    /**
     * @info 获取某个分类
     */
    //临时更新表
    public function get_practice_category($id){
        $result = $this->db
            ->query("select * from `practice_category` where `id` = {$id}")
            ->row_array();
        return $result;
    }
    //临时更新表
    public function fetch_practice_by_cid($p_c_id,$question_type){
        $practice = $this->db
            ->query("select * from `practice` where `p_c_id` = {$p_c_id} and `question_type` = {$question_type} and `online` = 1")
            ->result_array();
        return $practice;
    }   
    //获取一个practice
    //临时更新表
    public function get_practice($id){
        $practice = $this->db
            ->query("select * from `practice` where `id` = {$id}")
            ->row_array();
        return $practice;
    }
    //获取多个practice
    //临时更新表
    public function get_practice_by_ids($ids){
        if(empty($ids)) return false;
        if(is_array($ids)) $ids_str = implode(",",$ids);
        $practices = $this->db
            ->query("select * from `practice` where `id` in ({$ids_str})")
            ->result_array();
        return $practices;
    }
    /**
     * @info 获取学生某次练习
     */
    public function get_student_practice($id){
        $result = $this->db
            ->query("select * from `student_practice` where `id` = {$id}")
            ->row_array();
        return $result;
    }
    /**
     * @info 保存新的练习记录
     */
    public function insert_student_practice($practice_info){
        if($this->db->insert('student_practice',$practice_info)){
            return true;
        }
        return false;
    }
    /**
     * @info 修改练习记录
     */
    public function update_student_practice($s_p_id,$practice_info){
        if($this->db->update('student_practice',$practice_info,array('id'=>$s_p_id))){
            return true;
        }
        return false;
    }
    /**
     * @info 根据学生uid 获取练习的作业
     *
     */
    public function fetch_student_practice($uid,$p_c_id ='',$order='',$offset=0,$num='',$where=''){
        $sql = "select * from `student_practice`";
        $sql .= " where `uid` = {$uid}";
        if(!empty($p_c_id)){
            $sql .= " and `p_c_id` = $p_c_id";
        }
        if(!empty($where)){
            foreach($where as $where_val){
                $sql_where .= ' and '.$where_val[0].' '.$where_val[1].' '.$where_val[2];
            }
            $sql.= $sql_where;
        }
        $sql_order = '';
        if(!empty($order)){
            foreach($order as $order_field => $order_mode){
                $sql_order .= ' `'.$order_field.'`'." ".$order_mode;
            }
            $sql .= ' order by '.$sql_order;
        }
        if($offset != 0 || $num!=''){
            $sql_limit = ' limit '.$offset;
            if($num == ''){
                $num = -1;
            }
            $sql_limit .= ','.$num;
            $sql .= ' '.$sql_limit;
        }
        $questions = $this->db
            ->query($sql)
            ->result_array();
        return $questions;
    }
    public function get_practice_num($uid,$p_c_id){
        $result = $this->db
            ->query("select count(*) as sum from `student_practice` where `uid`={$uid} and `p_c_id` = {$p_c_id}")
            ->row_array();
        return $result['sum'];

    }
    /**
     * @info 获取练习记录
     */
    public function get_practice_record($uid,$p_c_id,$offset,$num){
        $result = $this->db
            ->query("select `id`,`expend_time`,`correct_num`,`question_num`,`start_time`,`s_p_type`,`is_submit` from `student_practice` where `uid`={$uid} and `p_c_id` = {$p_c_id} order by `is_submit` asc ,`end_time` desc,`start_time` desc limit {$offset},{$num}")
            ->result_array();
        return $result;
    }
    /**
     * @info 保存练习记录
     * @param data array(pid=>status)
     */
    public function save_practice_question_record($uid,$p_c_id,$data){
        $pid_group = array_keys($data);
        $pid_str = implode(",",$pid_group);
        $questions = $this->getQuestionsByPids($pid_group);
        $questions = $questions[3];
        $result = $this->db
            ->query("select * from `practice_question_record` where `user_id` = {$uid} and `practice_id` in ($pid_str) and `p_c_id` = $p_c_id")
            ->result_array();
        $pid_done_group = array();
        foreach($result as $result_val){
            $pid_done_group[] = $result_val['practice_id'];
        }
        array_unique($pid_done_group);
        $pid_undo_group = array_diff($pid_group,$pid_done_group);
        foreach($pid_group as $pid){
            $latest_status = $data[$pid]['status'];
            $input = $data[$pid]['input'];
            if(in_array($pid,$pid_done_group)){
                foreach($questions as $question){
                    if($question['practice_id'] == $pid){
                        $ext_sql = '';
                        if($latest_status == 1){
                            $ext_sql = ' ,`correct_num` = `correct_num` + 1 ';
                        }
                        $p_categories = explode(",",$question['category_id']);
                        foreach($p_categories as $p_category){
                            $this->db
                                ->query("update `practice_question_record` set `num`=`num`+1{$ext_sql},`latest_status`={$latest_status},`input`='{$input}' where `id` = {$pid} and `category_id`={$p_category}");
                        }
                    }
                }
            }else{
                foreach($questions as $question){
                    if($question['practice_id'] == $pid){
                        if($latest_status == 1){
                            $correct_num = 1;
                        }else{
                            $correct_num = 0;
                        }
                        $p_categories = explode(",",$question['category_id']);
                        foreach($p_categories as $p_category){
                            $db_data = array(
                                'user_id'=>$uid,
                                'practice_id'=>$pid,
                                'category_id'=>$p_category,
                                'p_c_id'=>$p_c_id,
                                'latest_status'=>$latest_status,
                                'num'=>1,
                                'input'=>$input,
                                'correct_num'=>$correct_num,
                                'is_show'=>1,
                                'updated_time'=>time()
                            );
                            $this->db->insert('practice_question_record',$db_data);
                        }
                    }
                }
            }
        }
    }
    /**
     * @info 知识点收藏题目错题数统计
     */
    public function favorites_question_statistics($uid,$p_c_id){
        $result = $this->db
            ->query("select a.`practice_id`,b.`name`,b.`id`,c.`latest_status`,a.`category_id` from `practice_favorites` as a left join `category` as b on a.`category_id` = b.`id` left join `practice_question_record` as c on a.`practice_id`=c.`practice_id` where a.`user_id` = {$uid} and a.`p_c_id` = {$p_c_id}")
            ->result_array();
        return $result;
    }

    /**
     * @info 根据知识点获取收藏题目
     */
    public function get_favorites_question($uid,$p_c_id,$category_id){
        $result = $this->db
            ->query("select * from `practice_favorites` where `user_id` = {$uid} and `p_c_id` = {$p_c_id} and  `category_id` = {$category_id}")
            ->result_array();
        $practice_group = array();
        foreach($result as $result_val){
            $practice_group[] = $result_val['practice_id'];
        }
        if(empty($practice_group)){
            return array();
        }
        $questions = $this->getQuestionsByPids($practice_group);
        return $questions;
    }
    /**
     * @info 获取今日练习
     */
    public function fetch_today_practice_by_uid($uid,$p_c_id){
        $start_time = strtotime(date("Y-m-d",time())." 00:00:00");
        $end_time = strtotime(date("Y-m-d",time())." 23:59:59");
        $sql = "select * from `student_practice` where `uid` = {$uid} and `start_time` >= {$start_time} and `start_time` <= {$end_time} and `p_c_id` = {$p_c_id}";
    
        $practice = $this->db->query($sql)->result_array();
        return $practice;
    }
    /**
     * @info 根据科目id 获取分类
     * @param subject_id 科目id
     *
     */
    public function get_categories_by_sid($subject_id){
        
        $categories = $this->db->query("select a.*,b.`grade`,b.`version`,b.`p_c_type` from `practice_category` as a left join `practice_category_info` as b on a.`id`=b.`p_c_id` and b.`online` = 1")->result_array();
        $second_categories = array();
        $third_categories = array();
        if(!empty($categories)){
            foreach($categories as $categories_val){
                if($subject_id == $categories_val['pid']){
                    $second_categories[] = $categories_val;
                }
            }
            foreach($second_categories as $second_categories_val){
                foreach($categories as $categories_val){
                    if($second_categories_val['id'] == $categories_val['pid']){
                        $third_categories[] = $categories_val;
                    }
                }
            }
        }

        return array($second_categories,$third_categories);

    }
    /**
     * @info 根据年级获取分类
     */
    public function get_categories_by_grade($grade){

        //小初高 三个级别
        if($grade == 1){
            $left_g = 0;
            $right_g = 8;
        }elseif($grade == 2){
            $left_g = 7;
            $right_g = 12;
        }elseif($grade == 3){
            $left_g = 11;
            $right_g = 16;
        }
        $categories = $this->db->query("select a.*,b.`grade`,b.`version`,b.`p_c_type`,c.`pid` as subject_id from `practice_category` as a left join `practice_category_info` as b on a.`id`=b.`p_c_id` left join `practice_category` as c on a.`pid` = c.`id` where `grade` > {$left_g} and `grade` < {$right_g} and b.`online` = 1 order by b.`grade` asc ")->result_array();
        return $categories;
        
    }

	public function getCategoriesByGradeId($grade_id){
		
        return $this->db->query("select a.*,b.`grade`,b.`version`,b.`p_c_type`,c.`pid` as subject_id, d.`user_num` from `practice_category` as a left join `practice_category_info` as b on a.`id`=b.`p_c_id` left join `practice_category` as c on a.`pid` = c.`id` left join `practice_participants_stats` as d on a.`id` = d.`p_c_id` where `grade` = {$grade_id} and b.`online` = 1 order by b.`grade` asc ")->result_array();

		
	}

    public function get_categories(){
        
        $categories = $this->db->query("select a.*,b.`grade`,b.`version`,b.`p_c_type`,c.`pid` as subject_id from `practice_category` as a left join `practice_category_info` as b on a.`id`=b.`p_c_id` left join `practice_category` as c on a.`pid` = c.`id` where `grade` is not NULL and b.`online` = 1 order by b.`grade` asc ")->result_array();
        return $categories;

    }
    
    public function get_category_info($p_c_id){
        $info = $this->db
            ->query("select a.*,b.*,c.`user_num` from `practice_category` as a  left join `practice_category_info` as b on a.`id` = b.`p_c_id` left join `practice_participants_stats` as c on a.`id` = b.`p_c_id` where a.`id` = {$p_c_id} and b.`online` = 1")
            ->row_array();
        return $info;
    }

    /**
     * @info 添加收藏
     */
    public function add_favorites($data){
        if(!($this->db->query("select * from `practice_favorites` where `user_id` = {$data['user_id']} and `practice_id` = {$data['practice_id']}")->num_rows())){
            if($this->db->insert('practice_favorites',$data)){
                return true;
            }
        }else{
            if($this->db->query("delete from `practice_favorites` where `user_id` = {$data['user_id']} and `practice_id` = {$data['practice_id']}")){
                return true;
            }
        }
        return false; 
    }
    /**
     * @info 收藏列表
     */
    public function favorites_list($uid){
        $result = $this->db->query("select * from `practice_favorites`  where `user_id` = {$uid}")->result_array();
        return $result;
    }

    /**
     * @info 根据id 获取 exercise
     */
    public function getExerciseQuestionsById($id){
        $sql_ext ='';
        if(isset($this->category_id)&&!empty($this->category_id)){
            $sql_ext = " and b.`category_id`= ".$this->category_id;
        }
        if(is_array($id)){
            $ids = implode(",",$id);
            $result = $this->db->query("select a.`id`,a.`qtype_id`,a.`level_id`,a.`asw`,b.`category_id` from `exercise` as a left join `exercise_category` as b on a.`id` = b.`question_id` where a.`id` in ( ".$ids." ) {$sql_ext}")->result_array();
        }else{
            $result = $this->db->query("select `id`,`qtype_id`,`level_id`,`asw` from `exercise`  where `id` =  {$id}")->row_array();
        }
        return $result;
    }

    /**
     * @info 根据id 获取 question 
     */
    public function getQestionQuestionsByIds($ids){
        $sql_ext ='';
        if(isset($this->category_id)&&!empty($this->category_id)){
            $sql_ext = " and b.`category_id`= ".$this->category_id;
        }
        if(is_array($ids)){
            $ids = implode(",",$ids);
            $result = $this->db->query("select a.`id`,a.`qtype_id`,a.`level_id`,a.`asw`,b.`category_id` from `question` as a left join `question_category` as b on a.`id` = b.`question_id`  where a.`id` in ( ".$ids." ) {$sql_ext}")->result_array();
        }else{
            $result = $this->db->query("select `id`,`qtype_id`,`level_id`,`asw` from `question`  where `id` =  {$id}")->row_array();

        }
        return $result;    
    }
    /**
     * @info 难度统计
     * @param $uid user_id int
     * @param $p_c_id  int
     */
    public function get_defficulty($uid,$p_c_id){
        $defficulty = $this->db
            ->query("select * from `practice_difficulty_statistics` where `user_id` = {$uid} and `p_c_id` = $p_c_id")
            ->row_array();
        return $defficulty;
    }
    /**
     * @info 知识点统计
     * @param $uid user_id int
     * @param $p_c_id  int
     */
    public function get_knowledge_statistics($uid,$p_c_id){
        $data = $this->db
            ->query("select * from `practice_knowledge_statistics` where `user_id` = $uid and `p_c_id` = $p_c_id")
            ->result_array();
        return $data;
    }
    public function getQuestionsFromMultiSource($data){

        $table_exercise_questions = $table_question_questions = array();
        $this->table_exercise_ids = $this->table_question_ids = array();

        foreach($data as $data_val){
            if($data_val['tn'] == 1){
                $table_exercise_pid[] = array('pid'=>$data_val['id'],'question_id'=>$data_val['question_id']);
                $this->table_exercise_ids[] = $data_val['question_id'];
            }elseif($data_val['tn'] == 2){
                $table_question_pid[] = array('pid'=>$data_val['id'],'question_id'=>$data_val['question_id']);
                $this->table_question_ids[] = $data_val['question_id'];
            }
        }
        if(!empty($this->table_exercise_ids)){
            $table_exercise_questions = $this->getExerciseQuestionsById($this->table_exercise_ids);
            $table_exercise_repeat = array();
            foreach($table_exercise_questions as $key=>$table_val){
                if(!in_array($table_val['id'],$table_exercise_repeat)){
                    $table_exercise_repeat[$key] = $table_val['id'];
                    foreach($table_exercise_pid as $table_exercise_pid_val){
                        if($table_exercise_pid_val['question_id'] == $table_val['id']){
                            $table_exercise_questions[$key]['practice_id'] =  $table_exercise_pid_val['pid'];
                        }
                    }
                }else{
                    foreach($table_exercise_repeat as $table_exercise_repeat_key=>$table_exercise_repeat_val){
                        if($table_exercise_repeat_val == $table_val['id']){
                            $table_exercise_questions[$table_exercise_repeat_key]['category_id'] .= ','.$table_val['category_id'];
                            unset($table_exercise_questions[$key]);
                            break;
                        }
                    }
                }
            }
            array_values($table_exercise_questions);
        }
        if(!empty($this->table_question_ids)){
            $table_question_questions = $this->getQestionQuestionsByIds($this->table_question_ids);
            $table_question_repeat = array();
            foreach($table_question_questions as $key=>$table_val){
                if(!in_array($table_val['id'],$table_question_repeat)){
                    $table_question_repeat[$key] = $table_val['id'];
                    foreach($table_question_pid as $table_question_pid_val){
                        if($table_question_pid_val['question_id'] == $table_val['id']){
                            $table_question_questions[$key]['practice_id'] =  $table_question_pid_val['pid'];
                        }
                    }
                }else{
                    foreach($table_question_repeat as $table_question_repeat_key=>$table_question_repeat_val){
                        if($table_question_repeat_val == $table_val['id']){
                            $table_question_questions[$table_question_repeat_key]['category_id'] .= ','.$table_val['category_id'];
                            unset($table_question_questions[$key]);
                            break;
                        }
                    }
                }
            }
            array_values($table_question_questions);
        }
        
        return array('table_exercise_questions'=>$table_exercise_questions,'table_question_questions'=>$table_question_questions);
    }
    
    /**
     * @info 根据pids 获取问题
     * @param ids array 
     */
    public function getQuestionsByPids($ids){
        $practices = $this->get_practice_by_ids($ids);
        if(empty($practices)) return false;
        $questions_cache = array();

        extract($this->getQuestionsFromMultiSource($practices));
        $q_question_contents = $this->student_homework_model->getQuestionContentsFroQue($this->table_question_ids);
        $e_question_contents = $this->student_homework_model->get_question_contents($this->table_exercise_ids);
        foreach($table_exercise_questions as $t_e_question){
            foreach($e_question_contents as $question_id=>$question){
                if($question_id == $t_e_question['id']){
                    $t_e_question['title'] = $question['title'];
                    $t_e_question['option'] = $question['option'];
                    $t_e_question['analysis'] = $question['analysis'];
                    if(in_array($t_e_question['practice_id'],$ids)){
                        $of = array_search($t_e_question['practice_id'],$ids);
                        $questions_cache[$of] = $t_e_question;
                    }
                    break;
                }
            }
        }
        foreach($table_question_questions as $t_q_question){
            foreach($q_question_contents as $question_id=>$question){
                if($question_id == $t_q_question['id']){

                    $t_q_question['title'] = $question['title'];
                    $t_q_question['option'] = $question['option'];
                    $t_q_question['analysis'] = $question['analysis'];
                    if(in_array($t_q_question['practice_id'],$ids)){
                        $of = array_search($t_q_question['practice_id'],$ids);
                        $questions_cache[$of] = $t_q_question;
                    }
                    break;
                }
            }
        }
        ksort($questions_cache);

        foreach($questions_cache as $questions_val){
            $new_questions_group[$questions_val['qtype_id']][] = $questions_val;
        }
        return $new_questions_group;

    }

    public function connect_redis($db){
        $this->load->model("redis/redis_model");
        if($this->redis_model->connect($db))
        {
            $redis = $this->cache->redis;
            return $redis;
        }
        return false;
    }
    public function get_resources($p_c_id,$type=NULL){
        $sql_ext = '';
        if($type){
            $sql_ext = " and `type` = {$type} ";
        }
        $resources = $this->db->query("select * from `practice_resources` where `p_c_id` = {$p_c_id} ".$sql_ext)->result_array();
        foreach($resources as $key => $resource){
            foreach($resource as $r_key => $val){
                $resource[$r_key] = stripslashes($val);
            }
            $resources[$key] = $resource;
        }
        return $resources;
    }
    /*
     * @info 获取知识点
     */
    public function get_category_by_ids($ids){
        $this->load->model('question/question_category_model');
        $ids_bak = array();
        foreach($ids as $key=>$ids_str){
            if(empty($ids_str)) continue;
            $temp_ids = explode(",",$ids_str);
            $status = true;
            foreach($temp_ids as $id){
                if(empty($id)) continue;
                $ids_bak[] = $id;
                $name = $this->get_root_category($id);
                if(!$name)continue;
                if(preg_match("/综合/",$name)){
                    $ids[$key] = $id;
                    $status = false;
                    $this->q_c_mapping[$id] = isset($this->q_c_mapping[$id]) ? array_merge($this->q_c_mapping[$id],$temp_ids):$temp_ids;
                   break;
                }
            }
            if($status){
                $ids[$key] = $id;
                $this->q_c_mapping[$id] = isset($this->q_c_mapping[$id]) ? array_merge($this->q_c_mapping[$id],$temp_ids):$temp_ids;
            }
        }
        if(empty($ids_bak)) return array();
        $ids = implode(",",$ids_bak);
        $result = $this->db
            ->query("select * from `category` where `id` in ({$ids})")
            ->result_array();
        $return_info = array();
        foreach($result as $val){
            $result_info[$val['id']] = $val;
        }
        return $result_info;
    }

    public function category_judge($ids){
        $this->load->model('question/question_category_model');
        $result = array();
        
        foreach($ids as $id){
            $name = $this->get_root_category($id);
            if(empty($name))break;
            if(preg_match("/.*综合.*/",$name)){
                $result[] = $id;
            }
        }
        return $result;
    }
    
    //获取错题信息  
    public function get_wrong_questions($uid,$p_c_id){
        $result = $this->db
            ->query("select a.*,b.`name` from `practice_question_record` as a left join `category` as b on a.`category_id` = b.`id` where a.`user_id` = {$uid} and a.`p_c_id` = {$p_c_id} and ( a.`latest_status` = 0 or a.`latest_status` = 1) and a.`is_show` = 1")
            ->result_array();
        $wrong_group = array();
        $category_info = array();
        $wrong_num = array();
        foreach($result as $result_val){
            if(!$this->category_judge(array($result_val['category_id'])))continue;
            $wrong_group[$result_val['category_id']][] =  $result_val['practice_id']; 
            $category_info[$result_val['category_id']] = $result_val['name'];
            if(isset($wrong_num[$result_val['category_id']])){
                $wrong_num[$result_val['category_id']] += $result_val['num'];
            }else{
                $wrong_num[$result_val['category_id']] = $result_val['num'];
            }
        }
        return array('wrong_group'=>$wrong_group,'category_info'=>$category_info,'wrong_num'=>$wrong_num);
    }

    /*
     * 获取某知识点错误题目
     */
    public function getWrongQuestionsByCategory($uid,$p_c_id,$category_id){
        $result = $this->db
            ->query("select `practice_id`,`category_id` from  `practice_question_record` where `user_id` = {$uid} and `p_c_id` = {$p_c_id} and `category_id` = {$category_id} and ( `latest_status` = 0 or `latest_status` = 1 ) and `is_show` = 1 ")
            ->result_array();   
        if(empty($result)){
            return array();   
        }
        $practice_group = array();
        foreach($result as $result_val){
            $practice_group[] = $result_val['practice_id'];
        }
        $questions = $this->getQuestionsByPids($practice_group);
        return $questions;
    }
    /*继续上次练习*/
    public function get_last_practice($uid,$p_c_id){
        $result = $this->db
            ->query("select `id`,`s_p_type`,`start_time` from `student_practice` where `uid` = {$uid} and `p_c_id`=$p_c_id and `is_submit`=0 order by `start_time` asc")
            ->row_array();
        return $result;
    }
    public function get_subject_type($id){
        $result = $this->db
            ->query("select * from `subject_type` where `id` = {$id}")
            ->row_array();
        return $result;
    }
    //有题专项统计
    public function special_count(){
        $result = $this->db
            ->query("select distinct `p_c_id` from `practice` where `online` = 1 and `question_type` = 2")
            ->result_array();
        $p_c_id_group = array();
        foreach($result as $val){
            $p_c_id_group[] = $val['p_c_id'];
        }
        return $p_c_id_group;
    }
    //统计未完成练习数量
    public function get_practice_unfinished_num($uid){
        $result = $this->db
            ->query("select `p_c_id`,count(*) as num from `student_practice` where `is_submit` = 0  and `s_p_type` in (1,2) and `uid`= {$uid} group by `p_c_id`")
            ->result_array();
        return $result;
    }

    private function get_root_category($id){
        if(empty($id)) return false;
        $result = $this->db->query(                                               
            "select parent.`name` from `category` as node,`category` as parent
            where (node.lft between parent.lft and parent.rgt) and               
            (parent.depth = 1) and node.id = $id"
        )
        ->row_array();                                                                       
        if(!empty($result))return $result['name'];
        return false;
    }

    /*
    private function _fileter_categories($categories){
    
        $category_temp = array();
        foreach($categories as $key=>$category)
            $category_name = $category['name'];
            foreach($category_group as $c_id=>$cn){
                if(preg_match("/$category_name/",$cn)){
                    $category_temp[$c_id][] = $category['id'];
                }elseif(preg_match("/$cn/",$category_name)){
                    unset($category_group[$cn]);
                }else{
                    $category_group[$c_id] = $category_name;
                }
            }
            $category_group[$category['id']] = $category_name;
            
        }
    }
    */


}

/*end of practice_model.php*/
