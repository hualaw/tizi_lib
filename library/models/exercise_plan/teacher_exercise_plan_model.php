<?php
require_once LIBPATH."models/exercise_plan/exercise_plan_model.php";
class Teacher_Exercise_Plan_Model extends exercise_plan_model{
    private $_table = 'paper_assign';
    private $_table_paper = 'paper_testpaper';
    private $_table_stu_paper = 'student_paper';

    protected $_redis=false;
    public function __construct(){
        parent::__construct();
        $this->load->model('exercise_plan/t_ex_plan_infrastructure_model','infra_model');
        $this->load->model("redis/redis_model");
    }

    //检测是否subject_id属于subject_type_id下
    //$s_id是数组
    function check_subject_id_legal($s_id,$subject_type_id){
        $this->load->model('question/question_subject_model');
        $group = $this->question_subject_model->get_subjects_by_tid($subject_type_id);
        if(in_array($s_id,$group)){
            return true;
        }
        return false;
    }

    //转换班级的ids
    //return array()
    function alpha_class_ids($classes,$to_num = false){
        if(!is_array($classes)){
            $this->load->helper('array');
            $classes = explode_to_distinct_and_notempty($classes);
        }
        foreach($classes as $k=>$v){
            $v = alpha_id_num($v,$to_num);
            $classes[$k] = $v;
        }
        return $classes;
    }

    // tizi2.0  查看某个班，某份作业的完成数
    //return array('finished'=>0,'total'=>1)
    function student_finish_num($class_id, $assignment_id){
        $this->load->model('class/classes_student');

        $student_ids = $this->classes_student->get_user_ids($class_id, 'user_id'); 
        $total = count($student_ids);
        if(!$total){
            return array('finished'=>0,'total'=>0);
        }

        $sql = "select student_id from student_homework where assignment_id = $assignment_id ";
        $sql .= " and is_completed = 1 ";
        $finished = $this->db->query($sql)->result_array();
        if(count($finished)==0){
            return array('finished'=>0,'total'=>$total);
        }
        $stu_ids = array();
        foreach($student_ids as $v){
            $stu_ids[] = $v['user_id'];
        }
        $f_count = 0 ;
        foreach($finished as $k=>$v){
            if(in_array($v['student_id'],$stu_ids)) $f_count ++;
        }
        return array('finished'=>$f_count,'total'=>$total);
    }

    //添加评语
    function insert_comment($d){
        $time = time();
        $sql = "insert into student_exercise_plan_comment(id,student_id,assignment_id,content,teacher_id,is_del,create_time) values(0,?,?,?,?,0,$time)";
        $arr = array($d['student_id'],$d['assignment_id'],$d['content'],$d['teacher_id']);
        return $this->db->query($sql,$arr);
    }

    //查看是否有评语
    function has_comment($assignment_id,$stu_id){
        $sql = "select count(1) as cc from student_exercise_plan_comment where assignment_id = $assignment_id and student_id = $stu_id";
        $res = $this->db->query($sql)->row(0)->cc;
        if($res)return true;
        return false;
    }

    //tizi 3.0   获取某个班级的作业   
    //update tizi4.0 2014-07-12   更换表
    function get_class_exercise($class,$uid,$page=1,$pagesize=10,$total=false,$before_time=null){
        $start = ($page-1)*$pagesize;
        if($start<1)$start = 0;
        $uid_sql = "";
        if($uid){
            $uid = intval($uid);
            $uid_sql = " hw.user_id=$uid ";
        }
        $select = "select hw.*,hp.subject_id,s.name as sname ";
        $_sql = " from {$this->_table} hw left join {$this->_table_paper} hp on hw.paper_id=hp.id 
                left join subject s on s.id=hp.subject_id where 1=1 
                and hw.class_id=? and hw.is_assigned=1 ";
        if($before_time){
            $before_time = " and start_time < ".strtotime(date('Y-m-d 18:00'));
            $_sql .= $before_time;
        }
        $order = " order by id desc ";
        $limit = " limit $start,$pagesize";
        $sql = $select.$_sql.$order.$limit;
        $arr = array($class);
        $res = $this->db->query($sql,$arr)->result_array();//echo $this->db->last_query();die;
        $extotal = false;
        if($total){
            $select = "select count(1) as num ";
            $extotal = $this->db->query($select.$_sql,$arr)->row(0)->num;
        }
        return array('exercise'=>$res,'total'=>$extotal);
    }

    //获取该老师、该班的 当前作业  (未到期(now<dead,未到期就是未检查) && 到期未检查的作业(dead<now and not checked))
    //tizi2.0 新见解：最新的没有check过的，不管到期了没，都是current homework
    function current_hw($teacher_id = 0 ,$class_id = 0){
        $teacher_id = intval($teacher_id); $class_id = intval($class_id);
        $now = time();
        $sql = "select * from homework_assign where user_id = $teacher_id and class_id = $class_id and is_assigned = 1 and (  is_checked is null or is_checked!=1) ORDER BY id DESC limit 1"; //上限1个
        $res = $this->db->query($sql)->result_array();//echo $this->db->last_query();die;
        if($res){
            return $res;
        }
        return null;
    }

    //tizi2.0 该老师、班级的 历史作业 , check过的就算历史作业
    //is_assigned = 0 的表示被删除的作业
    function overdue_hw($teacher_id = 0 ,$class_id = 0, $page = 1, $pagesize = 10 ){
        $teacher_id = intval($teacher_id); $class_id = intval($class_id);
        $now = time();
        $page = intval($page);
        if($page<1){
            $page = 1;
        }
        $pagesize = intval($pagesize);
        if($pagesize<1){
            $pagesize = 10;
        }
        $start = ($page-1)*$pagesize;
        $sql = "select * from homework_assign where user_id = $teacher_id and class_id = $class_id and is_assigned = 1 and is_checked = 1 ORDER BY id DESC limit $start,$pagesize ";
        $res = $this->db->query($sql)->result_array();//echo $sql;die;
        if($res){
            return $res;
        }
        return null;
    }

    //tizi2.0 获取某老师某班的历史作业总数
    function overdue_hw_count($teacher_id,$class_id){
        $teacher_id = intval($teacher_id);
        $class_id = intval($class_id);
        if(!$teacher_id || !$class_id) return 0;
        $sql = "select count(*) as num from homework_assign where user_id = $teacher_id and class_id = $class_id and is_assigned = 1 and  is_checked = 1  ";
        $res = $this->db->query($sql)->row(0)->num;//echo $sql;die;
        return $res;
    }

    /** tizi2.0 统计一次班级作业的情况:
        完成人数, complete_count
        平均耗时, avg_time
        平均正确率, avg_score
        平均正确率以上/下人数, $beyond_avg, under_avg
        未完成人数  $un_complete
    **/
    function statis_class_hw($student_infos,$assignment_id){
        $common_url = site_url()."teacher/homework/student/$assignment_id";
        $i = $avg_score = $complete_count = $un_complete = $beyond_avg = $under_avg = $avg_time = $t=0;
        //计算平均分，统计做作业的用时
        if($student_infos){
            $this->load->helper('handle_answer');
            foreach ($student_infos as $key=>$val){
                $val['name'] = sub_str($val['name']);
                $online_q = unserialize($val['s_answer']);
                if(!isset($online_q['online'])){
                   $online_q['online'] = null;
                }
                $val['condition'] = handle_person_answers_color($online_q['online']);
                
                if($val['is_completed']){
                    $val['url'] = $common_url."/{$val['student_id']}";
                    $complete_count ++;
                }else{
                    $val['url'] = null;
                }
                $student_infos[$key]=$val;
                // 计算平均分
                if(!empty($val['condition'])){
                    $i++;  
                    $avg_score += $val['condition']['score'];
                }
                if($val['expend_time']){
                    $t++; 
                    $avg_time += $val['expend_time'];
                }
            }
        }
        if($i){
            $avg_score /= $i; // 平均分 
            if($t) $avg_time /= $t; //平均用时
        }else{
            $avg_score = $avg_time = 0;
        }
        if($avg_score && $student_infos){
            foreach ($student_infos as $key=>$val){
                if(!empty($val['condition'])){
                    if($val['condition']['score']>=$avg_score) $beyond_avg++;  //均正确率以上人数
                }
            }
        }
        $all_people = count($student_infos);
        $un_complete = $all_people-$complete_count;
        $under_avg = $complete_count-$beyond_avg;
        $return = array();
        $return['un_complete'] = $un_complete;
        $return['under_avg'] = $under_avg;
        $return['complete_count'] = $complete_count;
        $return['avg_score'] = $avg_score;
        $return['beyond_avg'] = $beyond_avg;
        $return['under_avg'] = $under_avg;
        $return['avg_time'] = $avg_time;
        $return['student_infos'] = $student_infos;
        return $return;
    }

    //tizi2.0 每份作业的错题排行,包括题号和错题人数
    function wrong_question_list($assignment_id,$question_ids,$question_arr){
            $this->load->model('wrongquestion/wrongquestion_model','wq');
            $w_ques = array();
            if(!empty($question_ids)){
                $w_ques = $this->wq->getQuestionWrongs($assignment_id,$question_ids);          
                if(!(count($w_ques)==1 && $w_ques[0]['counts']==0)){
                    foreach($w_ques as $key=>$val){
                        $val['order'] = array_search($val['question_id'], $question_arr)+1;
                        $w_ques[$key] = $val;
                    }
                }else{ $w_ques = null;}
            }
            if(!empty($w_ques)){ // 排序错题，按照做错的人数
                $tmp = Array();
                foreach($w_ques as $ma){
                    $tmp[] = $ma["order"]; //能不能先按照题号排，再按照错误率？
                }
                array_multisort($tmp, $w_ques);
                $tmp = Array();
                foreach($w_ques as $ma){
                    $tmp[] = $ma["counts"]; // sort by countes 
                }
                array_multisort($tmp, SORT_DESC,$w_ques);
            }
            return $w_ques;
    }

    //tizi2.0 某次作业的排名
    function stu_rank($stu){
        if(!empty($stu) && is_array($stu)){
            $this->array_sort_by_keys($stu,array('correct_num'=>'desc','expend_time'=>'asc'));
            return $stu;
        }
        return array();
    }

    //[通用] 二维数组排序方法
    function array_sort_by_keys(&$arr, $sort = array()) {
        if (empty($sort) || !is_array($sort) || !$arr) {
            return null;
        }
        // 非连续的索引数组 或者 关联数组 先转化成以0开始的索引的数组
        $arr = array_values($arr);
        for ($i = 0; $i < count($arr); $i++) {
            for ($j = 0; $j < count($arr) - $i -1 ; $j++) {
                foreach ($sort as $k => $v) {

                    if (!array_key_exists($k, $arr[$j])
                        || !array_key_exists($k, $arr[$j + 1])) {
                        return NULL;
                    }

                    $tmp = $arr[$j][$k];

                    $tmp2 = $arr[$j + 1][$k];

                    if (strtolower($v[0]) == 'i') {
                        $tmp = strtolower($tmp);
                        $tmp2 = strtolower($tmp2);
                    }
                    if (stripos($v, 'asc') !== false) {
                        if ($tmp > $tmp2) {
                            $tmp = $arr[$j];
                            $arr[$j] = $arr[$j + 1];
                            $arr[$j + 1] = $tmp;
                            break;
                        } else if ($tmp < $tmp2) {
                            break;
                        }
                    } else {
                        if ($tmp < $tmp2) {
                            $tmp = $arr[$j];
                            $arr[$j] = $arr[$j + 1];
                            $arr[$j + 1] = $tmp;
                            break;
                        } else if ($tmp > $tmp2) {
                            break;
                        }
                    }
                }
            }
        }
    }

    //删除作业
    function del_assignment($assignment_id=0){
        $assignment_id = intval($assignment_id);
        $sql = "update {$this->_table} set is_assigned = 0 where id = $assignment_id ";
        $res = $this->db->query($sql);
        if($res){
            return array('msg'=>'删除成功','status'=>$res);
        }
        return array('msg'=>'删除失败','status'=>false);
    }

    //某作业 已作答人数
    function has_finished_stu($assignment_id,$class_id,$index='is_completed'){
        $sql = "select count($index) as num from {$this->_table_stu_paper} sh,classes_student cs where sh.paper_assign_id=? and sh.user_id = cs.user_id and cs.class_id=? and sh.$index=1";
        $arr = array($assignment_id,$class_id);
        $num = $this->db->query($sql,$arr)->row(0)->num;
        return $num;
    }

    //获取班级内学生总数 
    public function get_class_stu_num($class_id){
        $class_id = intval($class_id);
        $sql = "select count(*) as num from classes_student where class_id = $class_id";
        return $this->db->query($sql)->row(0)->num;
    }  

    //获取班级内 未/已下载作业 的 学生名称
    public function download_stu_names($assignment_id,$class_id){
        $sql = "select u.name , sh.is_download from student_homework sh,classes_student cs ,user u where sh.assignment_id=? and sh.student_id = cs.user_id and cs.class_id=? and u.id=sh.student_id";
        $arr = array($assignment_id,$class_id);
        $res = $this->db->query($sql,$arr)->result_array();
        return $res;
    }    

    /** tizi2.0  subject_type_id, 获取各个学段的教材版本
        group[0]['s_name'] = 初中语文
        group[0]['cat'][0]->name = 初中语文人教版
        group[1]['cat'][0]->second[0]['name'] = 人教版七年级下
    **/
    function get_all_cat_ver($subject_type_id){
        $this->load->model('question/question_subject_model');
        $group = $this->question_subject_model->get_subjects_by_tid($subject_type_id);
        if($group){
            foreach ($group as $k=>$v){
                $temp = $v; $v =array(); $v['s_id'] = $temp;
                $v['s_name'] = $this->question_subject_model->get_subject_name($v['s_id']);
                $v['cat'] = $this->infra_model->get_subject_course_root_id($temp);
                $group[$k] = $v;
            }
        }else{
            return null;
        }
        return $group;
    }

    // tizi2.0  通过depth==2的course_id来获取下面的course_id
    function course_depth34($c_id=31475){
        $c_id = intval($c_id);
        $this->load->model('exercise_plan/t_ex_plan_infrastructure_model','infra_model');
        $dep3 = $this->infra_model->get_course_category_depth3($c_id);
        if($dep3['category'] && is_array($dep3['category'])){
            foreach($dep3['category'] as $k=>$v){
                $d4 = $this->infra_model->get_course_category_depth3($v['id']);
                if($d4){
                    $v['dep4'] = $d4['category'];
                }else{
                    $v['dep4'] = null;
                }
                $dep3['category'][$k] = $v;
            }            
            return ($dep3['category']);
        }
        return null;
    }

    
}