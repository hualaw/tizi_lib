<?php
/**
 * @author saeed
 * @description 学生资料
 */

class Student_Data_Model extends LI_Model {

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @info 获取学生资料
     */
    public function get_student_data($uid){
        return $this->db->query("select * from `student_data` where `uid` = $uid")->row();
    }

	/** 获得学生和宠物的信息
	 * @param $uid
	 * @return mixed
	 */
	public function get_student_pet_data($uid){
		return $this->db->query("SELECT sd.*,sp.pet_name,sp.pet_exp_bonus,sp.pet_description,sp.level_need FROM `student_data` sd LEFT JOIN `study_pets` sp ON sd.pet_id = sp.id WHERE `uid` = {$uid}")->row();
	}

	/** 初始化学生信息表
	 * @param $uid
	 * @return mixed
	 */
	public function init_student_data($uid){
		$param = array(
			'uid' => $uid,			//用户id
			'pet_id' => 1,			//选中的宠物
			'exp' => 0,				//用户经验
			'aq_account_balance' => 0	//学生余额
		);

		if (!$this->get_student_data($uid)) {
			$this->db->trans_start();
			$this->db->insert('student_data', $param);
			if ($this->db->trans_complete() === false) {
				return false;
			}
			return true;
		}
	}
    // 保存学生信息
    public function save_student_data($uid,$data){

        $result = $this->db
            ->query("select * from `student_data` where `uid` = $uid")
            ->row_array();
        if(empty($result)){
            $data['uid'] = $uid;
            return $this->db->insert('student_data',$data);
        }else{echo 'update';exit;
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

	/** 更新学生信息
	 * @param $uid
	 * @param $param
	 * @return bool
	 */
	public function update_student_data($uid, $param) {
		$this->db->trans_start();
		$this->db->where('uid', $uid);
		$this->db->update('student_data', $param);
		if ($this->db->trans_complete() === false) {
			return false;
		}
		return true;
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
        if($grade_id>0 && $grade_id <= 12) $check_grade=true;
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

	/** 用户经验得到相应的等级
	 * @param $exp
	 * @return int
	 */
	public function exp_to_level($exp) {
		$arr = array(
			4 => 1,
			11 => 2,
			20 => 3,
			31 => 4,
			44 => 5,
			60 => 6,
			77=> 7,
			95 => 8,
			116 => 9,
			139 => 10,
			164 => 11,
			191 => 12,
			220 => 13,
			252 => 14,
			285 => 15,
			319 => 16,
			356 => 17,
			395 => 18
		);

		foreach ($arr as $ka => $va) {
			if ($exp <= $ka) {
				return $va;
			}
		}
		return 18;
	}

	/** 用户由等级获得相应等级需要的经验
	 * @param $level
	 * @return mixed
	 */
	public function level_to_exp($level) {
		return ($level - 1) * ($level - 1) + 4 * ($level - 1);
	}

	/** 根据用户当前的经验等级 算出前台等级进度条的width
	 * @param $level
	 * @param $exp
	 * @param $width
	 * @return int
	 */
	public function user_level_progress($level, $exp, $width){
		$level_exp_low = ($level == 1) ? 0 : ($this->level_to_exp($level));
		$level_exp_up = $this->level_to_exp($level + 1);

		return intval((($exp - $level_exp_low) / ($level_exp_up - $level_exp_low)) * $width) ;
	}

}
