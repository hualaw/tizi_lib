<?php
class Classes_student extends MY_Model{
	
	const JOIN_METHOD_REGISTER	= 1;				//通过注册-邀请码方式加入
	const JOIN_METHOD_TCREATE	= 2;				//通过教师生成账号直接加入班级 
	const JOIN_METHOD_INVITESITE= 3;				//通过tizi.com/invite/xxx
	const JOIN_METHOD_TLET		= 4;				//通过老师输入用户名，学号等方式加入
	
	public function __construct(){
        parent::__construct();
    }
    
    public function add($class_id, $user_id, $join_date, $join_method, $student_id = null){
		/**
		 * 当前规则：一个学生只能加入一个班级
		 */ 
		$res = $this->db->query("select id from classes_student where user_id=?", array($user_id))->result();
		if ($res){
			return false;
		}
		
		$this->db->trans_start();
		$this->db->query("insert into classes_student(class_id,user_id,join_date,join_method) 
			values(?,?,?,?)", array($class_id, $user_id, $join_date, $join_method));
		$this->db->query("update classes set stu_count=stu_count+1 where id=?", array($class_id));
		$user = $this->db->query("select student_id from user where id=?", array($user_id))->result_array();
		if (null === $user[0]["student_id"] && null === $student_id){
			$this->load->model("class/classes_student_create");
			$student_id = $this->classes_student_create->get_stuid(1);
			$this->db->query("update user set student_id=? where id=?", array($student_id, $user_id));
		} else {
			$student_id = $user[0]["student_id"];
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		// 2014-01-09 给新进来的学生布置未截止的作业
		$this->load->model('homework/homework_assign_model','ham');
		$this->ham->get_hw_to_new_stu($user_id,$class_id);
		return $student_id;
	}
    
    /**
     * 获取所有班级内所有学生的基本信息
     */ 
    public function get_cs($class_id){
		$result = $this->db->query("select a.id as csid,a.user_id,a.join_date,a.join_method,b.name,
			b.student_id,b.avatar,b.password from classes_student as a left join user as b on a.user_id=b.id where 
			a.class_id=?", array($class_id))->result_array();
		
		return $result;
	}
	
	/**
	 * 根据classes_student的primary获取一条数据
	 */
	public function get($csid, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_student where id=?", 
			array($csid))->result_array();
		return isset($result[0]) ? $result[0] : null;
	}
	
	/**
	 * 根据classes_student的class_id获取数据
	 */
	function get_user_ids($class_id, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_student where class_id=?", 
			array($class_id))->result_array();//echo $this->db->last_query();die;
		return $result;
	}
	/**
	 * 根据user_id获取加入班级的记录
	 */
	public function userid_get($user_id, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_student where user_id=?", 
			array($user_id))->result_array();
		return $result;
	}
	
	/**
	 * 根据classes_student的primary删除一条数据
	 */
	public function remove($csid, $class_id){
		$this->db->trans_start();
		$this->db->query("delete from classes_student where id=?", array($csid));
		if ($this->db->affected_rows() === 1){
			$this->db->query("update classes set stu_count=stu_count-1 where id=?", array($class_id));
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		return true;
	}
	
	/**
	 * 通过class_id和user_id删除一条classes_student
	 */
	public function remove_uc($class_id, $user_id){
		$this->db->trans_start();
		$this->db->query("delete from classes_student where class_id=? and user_id=?", array($class_id, 
			$user_id));
		if ($this->db->affected_rows() === 1){
			$this->db->query("update classes set stu_count=stu_count-1 where id=?", array($class_id));
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		return $this->db->affected_rows();
	}
}
/* end of classes_student.php */