<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class Classes_Manager {

	const JOIN_METHOD_REGISTER	= 1;				//通过注册-邀请码方式加入
	const JOIN_METHOD_TCREATE	= 2;				//通过教师生成账号直接加入班级
	const JOIN_METHOD_INVITESITE= 3;				//通过tizi.com/invite/xxx
	const JOIN_METHOD_TLET		= 4;				//通过老师输入用户名，学号等方式加入
	const JOIN_METHOD_REGCLASS	= 5;				//通过班级编号注册直接加入

	protected $_CI;

	public function __construct(){
		$this->_CI = & get_instance();
	}

	//判断班级是否存在，RETURN TRUE OR FALSE
	public function exists($class_id){
		$this->_CI->load->model("class/classes");
		$class_info = $this->_CI->classes->get($class_id, "class_status");
		if (isset($class_info["class_status"]) && $class_info["class_status"] == 0){
			return true;
		} else {
			return false;
		}
	}

	public function create($classname, $creator_id, $create_date, $subject_id, $extension = array()){
		$this->_CI->load->model("class/classes");
		$classes = $this->_CI->classes->creator_get($creator_id, "id");
		$class_number = self::class_number($creator_id);
		if (count($classes) >= $class_number){
			return array("code" => -10, "msg" => "超过数量限制", "max" => $class_number);
		}
		return $this->_CI->classes->create($classname, $creator_id, $create_date, $subject_id, $extension);
	}

	private function class_number($user_id){
		return Constant::TEACHER_CLASS_MAX_NUM;
	}

	//老师加入班级
	/**
	 * @return
	 * 1:加入成功
	 * -1:班级不存在
	 * -2:班级已解散
	 * -3:该成员已经在该班级里面了
	 * -127:未知原因，加入失败
	 */
	public function teacher2class($class_id, $user_id, $subject_type = false, $join_date = false){
		$subject_type or $subject_type = Constant::DEFAULT_SUBJECT_TYPE;
		$join_date or $join_date = time();
		$this->_CI->load->model("class/classes");
		$class_info = $this->_CI->classes->get($class_id, "class_status,creator_id");
		if ($class_info){
			if ($class_info["class_status"] == 1){
				return -2;
			} else {
				$this->_CI->load->model("class/classes_teacher");
				$bt = $this->_CI->classes_teacher->get_bt($user_id);	//get classes_teacher by teacher_id
				foreach ($bt as $value){
					if ($value["class_id"] == $class_id){
						return -3;
					}
				}
				$this->_CI->load->model("class/class_model");
				$res = $this->_CI->class_model->i_join_class($class_id, $user_id, $subject_type, $join_date);
				if ($res === 1){
					return 1;
				} else {
					return -127;
				}
			}
		} else {
			return -1;
		}
	}

	//学生加入班级
	/**
	 * @return
	 * 1:加入成功
	 * -1:班级不存在
	 * -2:班级已解散
	 * -3:班级管理员禁止成员加入
	 * -4:班级成员已经超过了额定数量
	 * -5:已经加入过班级，或者超过了加入班级的数量
	 */
	public function student2class($class_id, $user_id, $method, $join_date = false){
		$join_date or $join_date = time();
		$this->_CI->load->model("class/classes");
		$class_info = $this->_CI->classes->get($class_id);
		if ($class_info === null){
			return -1;
		}
		if ($class_info["class_status"] == 1){
			return -2;
		}
		if ($class_info["close_status"] == 1){
			return -3;
		}

		$this->_CI->load->model("class/classes_student_create");
		$create_number = $this->_CI->classes_student_create->ulog_total($class_id);
		//权限控制增加
		$this->_CI->load->library("credit");
		$userlevel_privilege = $this->_CI->credit->userlevel_privilege($class_info["creator_id"]);
		$max_student_number = $userlevel_privilege["privilege"]["class_onelimit"]["value"];
		if (($class_info["stu_count"] + $create_number) >= $max_student_number){
			return -4;
		}

		$this->_CI->load->model("class/classes_student");
		$student_id = $this->_CI->classes_student->add($class_id, $user_id, $join_date, $method);
		if ($student_id === false){
			return -5;
		}
		return 1;
	}

}
