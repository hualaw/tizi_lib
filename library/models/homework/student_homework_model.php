<?php
/**
 * @author saeed
 * @date   2013-8-28
 * @description 学生作业提交处理
 */

class Student_Homework_Model extends LI_Model{

	private $_redis=false;

    private $_img_url = array(
        'static.91waijiao.com'=>'tzstatic.oss.aliyuncs.com/static',
        'pic1.mofangge.com'=>'tzstatic.oss.aliyuncs.com',
        'oss-internal.aliyuncs.com'=>'oss.aliyuncs.com'
    );

    function __construct()
    {
        //$this->load->library('My_Redis');
        //$this->redis = $this->my_redis->connect();
        parent::__construct();
        if($this->connect_redis()){
            $this->_redis = true;
        }
    }

    /**
     * @info 保存学生提交的作业
     * @param assignment_id int 作业id
     * @param s_answer text 题号,试题答案,学生作答
     * @param correct_question text 正确题号
     * @param question_id 问题id
     * @param answer 正确答案
     * @param input 学生输入 
     */
    public function save_homework($s_work_id,$homeworkinfo){
        $this->db->where('id',$s_work_id);
        if($this->db->update('student_homework',$homeworkinfo)){
            return true;
        }
        return false;        
    }

    //统计完成作业人数
    public function count_assign_complete($aid){

        $result = $this->db
            ->query("select count(*) as num from `student_homework` where `assignment_id`={$aid} and `end_time` <> 0")
            ->row_array();
        
        return !empty($result)?$result['num']:0;
    
    }

    //通过assignment_id，只查出所有的学生id
    function get_stu_ids_by_aid($assignment_id){
        $sql = "select student_id from student_homework where assignment_id = $assignment_id ";
        return $this->db->query($sql)->result_array();
    }

    /**
     * @param $id assignment_id 
     * @info 根据 assignment_id 获取 s_answer
     */
    public function get_sanswer_by_assignid($id){
        $questions =  $this->get_question_list($id);
        $new_questions = array();
        $i = 1;
        foreach($questions as $question){
            if(isset($question->id))//add by tangxunye 2013.10.10
            {
                $i++;
                $new_question[$i]['question_id'] = $question->id;
                $new_question[$i]['input'] = '';
                $new_question[$i]['answer'] =  $question->asw;//单选项
                $new_question[$i]['order'] =  $i;
            }
        }
        return serialize($new_question);
    }
    public function set_assign_question_list($aid,$data){
        if(!$this->_redis){
            log_message('error_tizi',"redis connect faild. module:student_homework_model[assign_question_list]",
                array('aid'=>$aid,'data'=>$data));
            return;
        }
        $this->cache->redis->hset('assign_question_list',$aid,serialize($data));
    }
    public function reset_deadline($aid,$deadline){
        if(!$this->_redis){
             log_message('error_tizi',"redis connect faild. module:student_homework_model[reset_deadline]",
                array('aid'=>$aid,'deadline'=>$deadline));
            return;
        }
        if($this->cache->redis->zrem("deadline_list",$aid)){
            $this->cache->redis->zadd("deadline_list",$deadline,$aid);
        }
    }
    /**
     * @info 保存答案
     * @param s_answer  
     * @param student_id string 学号
     * @param assigment_id string 作业id
     */
    public function save_input($s_work_id,$s_answer,$expend_time = ''){
        if($this->db->query("update `student_homework` set `s_answer` = '{$s_answer}' where `id` = $s_work_id")){
            if(!empty($expend_time)){
                if($this->db->query("update `student_homework` set `expend_time` = `expend_time` + $expend_time where `id` = $id")){
                    return true;
                }else{
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @info 
     * @deadline int 作业截止时间
     * @aid int 作业id
     * @deadline  int 截止时间
     *
     */
    public function save_work_advance($data){
        if(!$this->_redis){
            log_message('error_tizi',"redis connect faild.module:student_homework_model[save_work_advance]",
                array('data'=>$data));
            return;
        }
        $this->cache->redis->zadd('deadline_list',$data['deadline'],$data['aid']);
        $this->cache->redis->hset('score',$data['aid'],$data['uid_list']);
    }

    /**
     * @param hid 学生作业id
     * 根据作业id 获取学生作业完成信息
     */
    public function get_homework_by_id($hid){
        $this->db->select('*');
        $this->db->from('student_homework');
        $this->db->join('student_homework_comment','student_homework_comment.student_homework_id=student_homework.id','left');
        $this->db->where('student_homework.id',$hid);
        $query = $this->db->get();
        $result =  $query->result();
        return $result[0];
    }
    /**
     * @info 截止前未提交作业
     */
    public function none_submit($student_id,$subject_id=''){
        $start_time = time();
        if(empty($subject_id)){
            $query = $this->db->query("select a.*,b.*,c.* from `homework_assign` as a right join `student_homework` as b on a.`id` = b.`assignment_id`  left join `homework_paper` as c on `a`.`paper_id` = c.`id` where  b.`student_id` = $student_id and a.`start_time`<{$start_time} and a.`deadline` > {$start_time} and b.`end_time` = 0");
        }else{
            $query = $this->db->query("select a.*,b.*,c.* from `homework_assign` as a right join `student_homework` as b on a.`id` = b.`assignment_id`  left join `homework_paper` as c on `a`.`paper_id` = c.`id` where  b.`student_id` = $student_id and a.`start_time`<{$start_time} and a.`deadline` > {$start_time} and b.`end_time` = 0 and c.`subject_id` = $subject_id");
        }
        return $query->result();
    }   

    /**
     * @info 获取作业列表
     * @param uid int
     */
    public function get_homework($uid, $s_h_id){
            $result = $this->db
                ->query("select d.`content` as comment_content,a.`start_time`,a.`end_time`,a.`assignment_id`,a.`student_id`,a.`correct_num`,b.`online_count`,b.`count`,a.`id`,b.`paper_id`,b.`online`,b.`start_time` as w_start_time,b.`deadline` as w_end_time,c.`subject_id`,b.`name` as title,b.`description` from `student_homework` as a left join `homework_assign` as b on a.`assignment_id` = b.`id` left join `homework_paper` as c on b.`paper_id` = c.`id` left join `student_exercise_plan_comment` as d on a.`assignment_id` = d.`assignment_id` and d.`student_id` = {$uid} and d.`is_del` = 0 where a.`student_id` = {$uid}  and a.`id` = ({$s_h_id}) order by b.`assign_time` desc,b.`paper_id` desc, b.`online` desc ")
                ->row_array();    
            return $result;
    }
    

    public function get_videos($ids){
        
        if(is_array($ids) && !empty($ids)){
            $video_ids_str = implode(",",$ids);
            return $result = $this->db
                ->query("select * from `student_video` where id in ({$video_ids_str})")
                ->result_array();
        }
        return array();
    
    }

    public function get_today_video(){
        $date = date("Y-m-d");
        $result = $this->db
            ->query("select * from `student_video` where `date` like '{$date}%' order by rand() limit 0,1")
            ->row_array();
        if($result){
            $result['task_type'] = 2;
        }
        return $result;
    }

    /**
     * @info 获取作业题目
     */
    public function get_question_list($pid){
        if(isset($pid)&&$pid){
            $new_questions = array();
            $result_one = $this->db->query("select * from `homework_question_type` where `paper_id` = $pid")->result();
            $result_two = $this->db->query("select a.*,b.*,c.`category_id` from `homework_question` as a left join `exercise` as b on a.`question_id` = b.`id` left join `exercise_category` as c on a.`question_id` = c.`question_id` left join `homework_question_type` as d on a.`qtype_id`=d.`id` left join `question_type` as e on d.`qtype_id` = e.`id` where a.`paper_id` = $pid and a.`is_delete` = 0 and e.`is_select_type` = 1")->result();
            $current_questions = array();
            foreach($result_two as $result_two_val){
                $current_questions[$result_two_val->id] = $result_two_val; 
            }
            if(!empty($result_one)){
                foreach($result_one as $result_one_val){
                    $question_order = explode(",",$result_one_val->question_order);
                    foreach($question_order as $question_order_val){
                        if(array_key_exists($question_order_val,$current_questions)){
                            $new_questions[$result_one_val->qtype_id][] = $current_questions[$question_order_val];
                            unset($current_questions[$question_order_val]);
                        }
                    }
                }
                $new_questions = array_merge($new_questions,$current_questions);
            }
            return $new_questions;
        }
        return false;
    }
    public function get_student_homework($uid,$aid){
        return $this->db->query("select a.*,a.`id` as s_work_id,b.`name`,b.`paper_id`,b.`start_time` as begin_time,b.`deadline`,b.`get_answer_way`,b.`description` from `student_homework` as a left join `homework_assign` as b on a.`assignment_id` = b.`id` where a.`student_id` = $uid and a.`assignment_id`= $aid")->row();
    }
    /**
     * @info 统计未完成题的数量
     * @param assignment_id int 作业id
     * @param student_id int 学生id
     */
    public function count_uncomplete($s_work_id){
        $result = $this->db->query("select `s_answer` from `student_homework` where `id` = $s_work_id")->result();
        if(!empty($result)){
            $result = $result[0];
            $data = unserialize($result->s_answer);
            $i = 0;
            foreach($data as $val){
                if(empty($val['input'])){
                    $i++;
                }
            }
            return $i;
        }
        return false;
    }
    /**
     * @info 根据成绩统计作业数量
     */
    public function count_homework_score($scope,$subject_id,$uid){

        $now = time();
        $sql = "select * from `homework_assign` as a left join `student_homework` as b on a.`id` = b.`assignment_id` left join `homework_paper` as c on a.`paper_id` = c.`id` where  b.`student_id` = $uid ";
        if(!empty($subject_id)){
            $subject_list = implode(",",$this->get_subjects_by_tid($subject_id));
            $sql .= "and c.`subject_id` in ($subject_list)";
        }
        if($scope == 1){
            $sql .= " and b.`score`<70 and b.`end_time` <> 0";
        }elseif($scope == 2){
            $sql .= " and b.`score` >=70 and b.`score` <90 ";
        }elseif($scope == 3){
            $sql .= " and b.`score` >=90 ";
        }elseif($scope == 4){//完成情况
            $sql .= " and b.`is_completed` = 0";
        }
        $sql .= " and a.`start_time` < ".$now;
        $result = $this->db->query($sql)->result();
        return $result;
    }
    /**
     * @param uid int 用户id
     * @param online int 在线状态
     * @info 根据 uid 获取所有作业情况
     */
    public function get_homework_by_uid($uid,$online='',$subject_id=''){
        $sql = "select * from `homework_assign` as a left join `student_homework` as b on a.`id` = b.`assignment_id` left join `homework_paper` as c on a.`paper_id` = c.`id` where b.`student_id` = $uid ";
        if(!empty($subject_id)){
            $subject_list = implode(",",$this->get_subjects_by_tid($subject_id));
            $sql .= " and c.`subject_id` in ($subject_list)";
        }
        if (!empty($online)){
            if($online == 1){
                $sql  .= " and a.`online` =1";
            }else{
                $sql  .= " and a.`online` =0";
            }
        }
        $sql .= " and a.`start_time` <".time();
        $result =  $this->db->query($sql)->result();
        return $result;
    }

    public function get_question_answer($question_id){
        $question = $this->db->query("select * from `exercise` where `id` = {$question_id}")->row();
        return $question->asw;
    }
    
    /**
     * @info 获取用户排名
     */
    public function fetch_student_rank($s_work_id,$score){
        $result = $this->db->query("select * from `student_homework` where `id` = $s_work_id")->row();
        $list = $this->db->query("select * from `student_homework` where `assignment_id` = {$result->assignment_id}  group by `score` order by `score` desc")->result();
        $pro_key = '';
        foreach($list as $key=>$val){
            if($val->score > $score){
                $pro_key = $key +1;
            }elseif($val->score == $score){
                return $key+1;
            }elseif($val->score < $score){
                return $pro_key;
            }
        }
    }
    /**
     * @info 获取所有科目
     */
    public function get_subjects(){
        $this->load->model('question/question_subject_model');
        return $this->question_subject_model->get_subjects();
        //return $this->db->query("select a.`id`,b.`id` as type,b.`name` from `subject` as a left join `subject_type` as b on a.`type`=b.`id`")->result();
    }
    /**
     * @info 获取科目类型
     */
    public function get_subject_type(){
        $this->load->model('question/question_subject_model');
        return $this->question_subject_model->get_subject_type(true,'homework');
        //return $this->db->query("select * from `subject_type` where id<=9")->result();//暂时屏蔽科学，到时统一掉用xunye的该方法
    }

    /**
     * @info 根据科目类型获取科目
     */
    public function get_subjects_by_tid($type){
        $this->load->model('question/question_subject_model');
        return $this->question_subject_model->get_subjects_by_tid($type);
        //$result = $this->db->query("select `id` from `subject` where `type` = $type")->result();
        //$group = array();
        //foreach($result as $val){
        //    $group[] = $val->id;
        //}
        //return $group;
    }
    /**
     * @info homework_asign
     */
    public function get_stu_homework($uid,$assignment_id){
        $result = $this->db->query("select a.`get_answer_way`,a.`deadline`,b.*,c.* from `homework_assign` as a left join `student_homework` as b on a.`id` = b.`assignment_id` left join `homework_paper` as c on a.`paper_id` = c.`id` where a.`id` = $assignment_id and b.`student_id` = $uid")->row();
        return $result;
    }
    /**
     * @info homework_asign
     */
    public function get_homework_assign($aid){
        $result = $this->db->query("select * from `homework_assign` where `id` = $aid")->row();
        return $result;
    }
    /**
     * @info 根据分类id 查询分类
     */
    public function fetch_category_by_ids($ids){
        $result = $this->db->query("select a.`question_id`,b.`id`,b.`name` from `exercise_category` as a left join `category` as b on a.`category_id` = b.`id` where a.`question_id` in ($ids)")->result();
        return $result;
    }
    /**
     * @info 根据id 获得问题
     */
    public function get_question_by_ids($ids){
        $result = $this->db->query("select a.*,b.`category_id` from `exercise` as a left join `exercise_category` as b on a.`id` = b.`question_id` where a.`id` in ($ids)")->result();
        foreach($result as $result_val){
            $new_arr[$result_val->id] = $result_val;
        }
        return $new_arr;
    }



    function get_homework_by_student_id_and_assignment_id($stu_id,$assignment_id){
        $sql = "select s.*,u.name from student_homework s left join user u on u.id=s.student_id where s.student_id=$stu_id and s.assignment_id=$assignment_id";
        return $this->db->query($sql)->result_array();
    }
    

    /**
     * @info 根据学生id,获取教师信息
     */
    public function get_teacher($uid,$subject_id){
        $arr = $this->db->query("select c.`name` from `classes_student` as a left join `classes_teacher` as b  on a.`class_id` = b.`class_id` left join `user` as c on b.`teacher_id` = c.`id`  where a.`user_id` = $uid and b.`subject_id` = $subject_id")->row();
        return $arr;
    }
    
    public function connect_redis(){
		$this->load->model("redis/redis_model");
        if($this->redis_model->connect('timer'))
        {
            return true;
        }
        return false;
    }

    /**
     * @param uid 用户id
     * @info subject teacher
     */
    public function subject_teacher($uid){
        $result = $this->db->query("select * from `classes_student` as a right join `classes_teacher` as b on a.`class_id` = b.`class_id` where a.`user_id` = {$uid} ")->result();
        return $result;
    }
    /**
     * @info get paper
     */
    public function get_paper_by_pid($pid){
        $result = $this->db->query("select `name` from `homework_paper` where `id` = {$pid}")->row();
        if(empty($result)){
            return false;
        }
        return $result;
    }
    
    /**
     * @info 查询学生所在班级
     */

    public function fetch_user_class($uid){
        $result = $this->db->query("select * from `classes_student` as a left join `classes` as b on a.`class_id` = b.`id`  where a.`user_id` = $uid")->row();   
        if(empty($result)){
            return false;
        }else{
            return $result;
        }
    }
    public function fetch_user_parents($uid){
        $result = $this->db->query("select * from `parents_kids` as a left join `parents` as b on a.`parent_user_id` = b.`user_id` where a.`kid_user_id` = $uid")->result();   
        if(!empty($result)){
            return $result;
        }
        return false;
    }
    public function get_school_by_id($school_id){
        $res = $this->db->query("select `schoolname` from `classes_schools` where `id` = $school_id")->row();
        if(!empty($res)){
            return $res->schoolname;
        }
        return '';
    }
    public function get_stype_by_pid($id){
        return $this->db->query("select b.`id` from `homework_paper` as a left join `subject` as b on a.`subject_id` = b.`id` left join `subject_type` as c on b.`type` = c.`id` where  a.`id` = {$id}")
            ->row_array();
    }

    //获取作业问题内容
    public function get_question_contents($questions,$tn = 'exercise_text'){
        if(is_array($questions) && !empty($questions)){
            $questions_str = implode(",",$questions);
            $contents =  $this->db
                ->query("select `id`,`body`,`analysis` from `{$tn}` where `id` in ($questions_str)")
                ->result_array();
            $result  = array();
            $temp_arr = array();

            $contents = $this->_replace_img_url($contents);

            foreach($questions as $question_id){
                foreach($contents as $val){
                    if($val['id'] == $question_id){
                        $temp_arr[] = $val;           
                        break;
                    }
                }
            }
            $contents = $temp_arr;
            foreach($contents  as $key=>$content){
                $content = $this->separateQuestion($content);
                $result[$content['id']] = $content;
            }
            return $result;
        }
        return false;
    }

    public function separateQuestion($content){

        $body = $content['body'];
        $title = '';
        $option = array();
        $analysis = '';
        if(preg_match_all("/.*(?=A[．|.])/s",$body,$matches)){
            if(!isset($matches[0][0])) goto tran;
            $title = $this->_remove_attr($matches[0][0]);
            if(preg_match_all("/.*(A[．|.].*?(?=\bB[．|.]))/s",$body,$matches)){
                if(!isset($matches[1][0])) goto tran;
                $option[] = $this->_remove_attr($matches[1][0]);
            }else{
                goto tran;
            }
            if(preg_match_all("/.*(B[．|.].*?(?=\bC[．|.]))/s",$body,$matches)){
                if(!isset($matches[1][0])) goto tran;
                $option[] = $this->_remove_attr($matches[1][0]);
            }else{
                goto tran;
            }
            if(preg_match("/.*C[．|.]/s",$body)){
                if(preg_match_all("/.*(C[．|.].*?(?=\bD[．|.]))/s",$body,$matches)){
                    $option[] = $this->_remove_attr($matches[1][0]);
                }elseif(preg_match_all("/.*(C[．|.].*)/s",$body,$matches)){
                    $option[] = $this->_remove_attr($matches[1][0]);
                }
            }   
            if(preg_match_all("/.*(D[．|.].*)/s",$body,$matches)){
                $option[] = $this->_remove_attr($matches[1][0]);
            }
        }else{
            tran:{
                $title = $body;
                $option = array();
            }
        }
        $analysis = $this->_remove_attr($content['analysis']);
        $answer = isset($content['answer'])?$this->_remove_attr($content['answer']):'';
        $content['title'] = $title;
        $content['option'] = $option;
        $content['analysis'] = $analysis;
        $content['answer'] = $answer;
        return $content;
    }
    
    //纸上作业问题内容
    public function getPaperQuestionContents($questions){
        $result  = array();
        $questions[] = 19;
        if(is_array($questions) && !empty($questions)){
            $questions_str = implode(",",$questions);
            $contents =  $this->db
                ->query("select `id`,`body`,`analysis`,`answer` from `exercise_text` where `id` in ($questions_str)")
                ->result_array();

            $contents = $this->_replace_img_url($contents);

            $temp_arr = array();

            foreach($questions as $question_id){
                foreach($contents as $val){
                    if($val['id'] == $question_id){
                        $temp_arr[] = $val;           
                        break;
                    }
                }
            }
            $contents = $temp_arr;
            foreach($contents  as $key=>$content){
                $result[$content['id']]['title'] = $content['body'];
                $result[$content['id']]['answer'] = $this->_remove_attr($content['answer']);
                $result[$content['id']]['analysis'] = $this->_remove_attr($content['analysis'],true);
            }
        }
        return $result;
    }

    public function paperQuestionHandle($content){
        
        $data = $this->_replace_img_url(array($content));
        $content['title'] = $data[0]['body'];
        $content['analysis'] = $this->_remove_attr($content['analysis'],true);
        return $content;
    }

    public function getQuestionContentsFroQue($questions){
        return $this->get_question_contents($questions,'question_text');
    }

    public function getQuestionsOrderType($questions){

        $result  = array();
        if(is_array($questions) && !empty($questions)){
            $questions_str = implode(",",$questions);
            $result =  $this->db
                ->query("select a.`id`, b.`id` as type_id, b.`name` from `exercise` as a left join `question_type` as b on a.`qtype_id` = b.`id` where a.`id` in ($questions_str)")
                ->result_array();

            $tem = array();
            foreach($questions as $q_id){
                foreach($result as $val){
                    if($q_id == $val['id']){
                        $tem[]  = $val;            
                    }
                }
            }
            $result  =  $tem;
        }
        return $result;
    }

    //删除作业
    public function deleteHomework($assign_id){
        
        return $this->db->query("UPDATE  `student_task` INNER JOIN `student_homework` ON `student_task`.index_value = `student_homework`.id 
             SET    `student_task`.is_delete = '1'    WHERE  `student_homework`.assignment_id = {$assign_id}");

    }

    //获取问题所属章节
    public function get_question_course($question_ids){
    
        if(is_array($question_ids)){
            $question_str = implode(",",$question_ids);
        }
        $result = $this->db->query("select * from `exercise_course` as a left join `course` as b on a.`course_id` = b.`id` where a.`question_id` in ({$question_str})")
            ->result_array();
        return $result;
    }

    /**
     * 根据assignment_id获取做这个作业的相关信息
     * @param int $assignment_id
     */
    function get_all_stu_homework($assignment_id , $time=false){
        $sql = "select u.name , u.student_id as student_in_class_id ,st.sex, s.* from student_homework s left join user u on u.id=s.student_id left join student_data st on st.uid = u.id where assignment_id=$assignment_id ";
        if($time && $time<5){
            $time2 = $time*900 ; // to seconds
            $time1 = $time2 - 900 ;
            $sql .= " and expend_time > $time1 and expend_time <= $time2";
        }elseif($time >=5 ){
            $time1 = 3600;
            $sql .= " and expend_time > $time1 ";
        }
        // echo $sql;
        return  $this->db->query($sql)->result_array();
    }

    //删除某个学生的作业
    function del_student_homework($user_id,$assignment_id=0){
        $sql = "delete from student_homework where student_id = $user_id ";
        if($assignment_id){
            $sql .= "and assignment_id = $assignment_id";
        }
        $this->db->query($sql);
    }

    function getAssignDataByAid($aid){
        
        $data = $this->db
            ->query("select a.*,b.`user_id` as teacher_user_id from `homework_assign` as a left join `homework_paper` as b on a.`paper_id` = b.`id` where a.`id` = {$aid}")
            ->row_array();
        return $data;

    }

    /*---------------------------------------------------------------*/
    /**
     * @info 获取科目信息
     * @param $sid 科目id
     */
    public function get_subject_type_info($sid){
        $this->load->model('question/question_subject_model');
        return $this->question_subject_model->get_subject_type_info($sid);
        //$result = $this->db
        //    ->query("select b.`id`,b.`name` from `subject` as a left join `subject_type`  as b on a.`type` = b.`id` where a.`id` = {$sid}")
        //    ->row_array();
        //return $result;
    }
    //判断班级内是否有学生打开过作业
    public function homework_status($assign_id){
        $res = $this->db
            ->query("select * from student_homework where `assignment_id` = {$assign_id} and `start_time` <> 0")
            ->result_array();
        if(!empty($res)) return true;
        /*
        if($this->connect_redis()){
            $redis = $this->cache->redis; 
            if($redis->keys("*_".$assign_id)) return true;
        }
         */
        
        return false;
    }

    public function getStudentDataByUids($uid_group){
        $uid_str = implode(",",$uid_group);
        $result = $this->db
            ->query("select b.`id` as uid,a.`area`,b.`name` from `student_data` as a right join `user` as b on a.`uid` = b.`id` where b.`id` in ({$uid_str})")
            ->result_array();
        return $result;
    }
    
    private function _replace_img_url($content){
        foreach($content as $key=>$val){
            foreach($this->_img_url as $img_k => $img_v){
                if(isset($val['body'])){
                    $val['body'] = str_replace($img_k,$img_v,$val['body']);
                }
                if(isset($val['analysis'])){
                    $val['analysis'] = str_replace($img_k,$img_v,$val['analysis']);
                }
            }
            $content[$key] = $val;
        }
        return $content;
    }

    private function _remove_attr($text,$status = true){
        
        if($status){
            $text = strip_tags($text,"<IMG><u><sup><sub>");
        }else{
            $attr_group = array('style','class');
            foreach($attr_group as $attr){
                $text =  preg_replace( '/'.$attr.'=(["\'])[^\1]*?\1/i', '', $text, -1);
                $text =  preg_replace( '/'.$attr.'=.*?\s/i', '', $text, -1);
            }
        }
        return trim($text);
    }



}

/*end of student_homework_model.php*/
