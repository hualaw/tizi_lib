<?php
require_once('data_model.php');

class Student_Data_Model extends Data_Model {

    protected $_table='student_data';
    protected $_user_id='uid';

    function __construct()
    {
        parent::__construct();
    }

    public function get_student_data($user_id)
    {
        return parent::get_data($user_id);
    }

    public function update_student_data($user_id,$data)
    {
        if(!is_array($data)||empty($data)) return false;
        return $this->update_data_array($user_id,$data);
    }

    public function update_student_gender($user_id,$gender)
    {
        if(!$gender) return false;
        return $this->update_data($user_id,$gender,'sex');
    }

    public function update_student_qq($user_id,$qq)
    {
        if(!$qq) return false;
        return $this->update_data($user_id,$qq,'qq');
    }

    public function update_student_parent_phone($user_id,$parent_phone)
    {
        if(!$parent_phone) return false;
        return $this->update_data($user_id,$parent_phone,'parent_phone');
    }

    public function update_student_school_id($user_id,$school_id)
    {
        if(!$school_id) return false;
        return $this->update_data($user_id,$school_id,'school_id');
    }

    public function update_student_register($user_id,$parent_phone,$qq)
    {
        if(!$parent_phone&&!$qq) return false;
        return $this->update_data_array($user_id,array('parent_phone'=>$parent_phone,'qq'=>$qq));
    }

    public function update_student_detail($user_id,$gender,$qq)
    {
        if(!$gender) return false;
        return $this->update_data_array($user_id,array('sex'=>$gender,'qq'=>$qq));
    }

    public function update_student_survey($user_id,$gender,$school_id)
    {
        if(!$gender||!$school_id) return false;
        return $this->update_data_array($user_id,array('sex'=>$gender,'school_id'=>$school_id));
    }

    /**
     * @info 获取学生资料
     */
    //public function get_student_data($uid){
    //    return $this->db->query("select * from `student_data` where `uid` = $uid")->row();
    //}

    // 保存学生信息
    public function save_student_data($uid,$data){

        $result = $this->db
            ->query("select * from `student_data` where `uid` = $uid")
            ->row_array();
        if(empty($result)){
            $data['uid'] = $uid;
            return $this->db->insert('student_data',$data);
        }else{
            $this->db->where("uid",$uid);
            return $this->db->update('student_data',$data);
        }

    }

    public function getStudentDataByUids($uid_group){
        $uid_str = implode(",",$uid_group);
        $result = $this->db
            ->query("select b.`id` as uid,a.`area`,b.`name` from `student_data` as a right join `user` as b on a.`uid` = b.`id` where b.`id` in ({$uid_str})")
            ->result_array();
        return $result;
    }

    /**
     * @info 更新用户性别
     */
    public function update_user_sex($uid,$sex){
        if(!$sex) return false;
        $result = $this->db->query("select * from `student_data` where `uid` = {$uid}")->row();
        if(empty($result)){
            if($this->db->query("insert `student_data` (`uid`,`sex`)values($uid,$sex)")){
                return true;
            }
        }else{
             if($this->db->query("update `student_data` set `sex` = $sex where `uid` = $uid")){
                return true;
            }           
        }
        return false;
    }

    public function update_user_qq($uid,$qq){
        if(!$qq) return false;
        $result = $this->db->query("select * from `student_data` where `uid` = {$uid}")->row();
        if(empty($result)){
            if($this->db->query("insert `student_data` (`uid`,`qq`)values($uid,$qq)")){
                return true;
            }
        }else{
             if($this->db->query("update `student_data` set `qq` = $qq where `uid` = $uid")){
                return true;
            }           
        }
        return false;
    }

    public function update_parent_phone($uid,$parent_phone){
        if(!$parent_phone) return false;
        $result = $this->db->query("select * from `student_data` where `uid` = {$uid}")->row();
        if(empty($result)){
            if($this->db->query("insert `student_data` (`uid`,`parent_phone`)values($uid,$parent_phone)")){
                return true;
            }
        }else{
             if($this->db->query("update `student_data` set `parent_phone` = $qq where `uid` = $uid")){
                return true;
            }           
        }
        return false;
    }

   //获取学生地区信息
    public function get_student_area($uid){
        if(empty($uid)) return array();
        if(!is_array($uid)){
            $res = $this->db
                ->query("select c.`name` from `classes_student` as a  left join `classes` as b on a.`class_id` = b.`id` left join `classes_area` as c on (b.`city_id` = c.`id`) or (b.`province_id` = c.`id`) where a.`user_id` = {$uid}")
            ->result_array();
            return !empty($res)?$res[count($res)-1]:array();
        }else{
            $uid_str = implode(",",$uid);
            $res = $this->db
                ->query("select a.`user_id` as uid ,c.`name` from `classes_student` as a  left join `classes` as b on a.`class_id` = b.`id` left join `classes_area` as c on (b.`city_id` = c.`id`) or (b.`province_id` = c.`id`) where a.`user_id` in ({$uid_str})")
            ->result_array();
        }
        return $res;
    }

    /*check grade*/
    function check_grade($grade_id)
    {
        $check_grade = false;
        if($grade_id>0 && $grade_id <= 14) $check_grade=true;
        return $check_grade;
    }

    function get_grade_video($mygrade)
    {
        $grade_video=1;
        switch($mygrade)
        {
            case 1:
            case 2:
            case 3: $grade_video=3;break;//初中
            case 4:
            case 5:
            case 6:
            case 13: 
            case 14:$grade_video=4;break;//高中
            case 7:
            case 8:
            case 9: $grade_video=1;break;//小学1-3
            case 10:
            case 11:
            case 12:$grade_video=2;break;//小学4-6
            default:$grade_video=1;break;
        }
        return $grade_video;
    }

    public function get_user_grade($uid){
        $result = $this->db
            ->query("select `register_grade` from `user` where `id` = {$uid}")
            ->row_array();
        if(!empty($result)){
            return $result['register_grade'];
        }
        return false;
    }
}
