<?php

class Classes_Teacher extends LI_Model{
	
    public function __construct(){
        parent::__construct();
    }
    
    public function update_class_subject($class_id, $teacher_id, $subject_id){
		$this->db->query("update classes_teacher set subject_id=? where class_id=? and teacher_id=?", 
			array($subject_id, $class_id, $teacher_id));
		return $this->db->affected_rows();
	}
	
	/**
	 * 获取一个老师加入的班级
	 */
	public function getall($teacher_id){
		$this->load->model("class/class_model");
		return $this->class_model->g_teacher_classinfo($teacher_id);
	}
	
	/**
	 * 获取一个老师加入的班级(不联表)
	 */ 
	public function get_bt($teacher_id, $fields = "*", $order = "class_id DESC"){
		$result = $this->db->query("select {$fields} from classes_teacher where 
			teacher_id=? order by {$order}", array($teacher_id))->result_array();
		return $result;
	}

	//通过老师id 和 班级id  来获取其他信息
	public function get_teacher_class_info($teacher_id, $class_id){
		$teacher_id = intval($teacher_id);
		$class_id = intval($class_id);
		$result = $this->db->query("select * from classes_teacher where class_id=? and teacher_id=?",array($class_id, $teacher_id))->result_array();
		return $result;
	}
	
	/**
	 * 获取一个班级的所有老师(不联表)
	 */
	public function get_idct($class_id, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_teacher where 
			class_id=?", array($class_id))->result_array();
		return $result;
	}
	
	/**
	 * 获取一个班级的所有老师
	 */
	public function get_ct($class_id){
		$result = $this->db->query("select a.id as ctid,a.teacher_id,a.subject_id,a.join_date,b.name,
			b.avatar from classes_teacher as a left join user as b on a.teacher_id=b.id where 
			a.class_id=? order by a.id asc", array($class_id))->result_array();
		$this->load->model("question/question_subject_model");
		$subjects = $this->question_subject_model->get_subject_type();
		foreach ($result as $key => $value){
			$result[$key]["subject_name"] = isset($subjects[$value["subject_id"]]) ? $subjects[$value["subject_id"]] : "未指定";
		}
		
		return $result;
	}
		
	/**
	 * 根据classes_teacher的primary获取一条数据
	 */ 
	public function get($ctid, $fields = "*"){
		$result = $this->db->query("select {$fields} from classes_teacher where id=?", 
			array($ctid))->result_array();
		return isset($result[0]) ? $result[0] : null;
	}
	
	/**
	 * 根据classes_teacher的primary删除一条数据
	 */
	public function remove($ctid, $class_id){
		$this->db->trans_start();
		$this->db->query("delete from classes_teacher where id=?", array($ctid));
		if ($this->db->affected_rows() === 1){
			$this->db->query("update classes set tch_count=tch_count-1 where id=?", array($class_id));
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		return true;
	}
	
	/**
	 * 通过teacher_id和class_id删除一个关系
	 */ 
	public function tc_remove($class_id, $teacher_id){
		$this->db->trans_start();
		$this->db->query("delete from classes_teacher where class_id=? and teacher_id=?", 
			array($class_id, $teacher_id));
		if ($this->db->affected_rows() === 1){
			$this->db->query("update classes set tch_count=tch_count-1 where id=?", array($class_id));
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === false){
			return false;
		}
		return true;
	}
	
	/**
	 * 创建一条老师和班级的绑定信息，并更新班级老师数量(trans)
	 */ 
	public function create($class_id, $teacher_id, $subject_id, $join_date){
		$this->load->model("class/class_model");
		return $this->class_model->i_join_class($class_id, $teacher_id, $subject_id, $join_date);
	}
	
	/***--------------------------------------old-------------------------------***/
	
	/*获取老师所在的班级信息*/
	//classes_teacher中的subject_id是subject type id， 不分初中高中，只分学科
	function get_classes_by_tch($teacher_id , $subject_id=false){
		$this->db->select('classes.*,user.name as creator_name,classes_teacher.subject_id');
		$this->db->from('classes_teacher');
		$this->db->join('classes',"classes.id=classes_teacher.class_id AND classes_teacher.teacher_id={$teacher_id}",'left');
		$this->db->join('user', 'user.id=classes.creator_id','left');
		if($subject_id){
				$this->db->where('classes_teacher.subject_id',$subject_id);
		}
		$this->db->where('classes.close_status',0);
		$this->db->order_by('classes.id', 'desc');
		$arr_result = $this->db->get()->result_array();
		if(!empty($arr_result))
		{
			foreach($arr_result as &$value)
			{
				$this->prase_data($value);
			}
		}
		return $arr_result;
	}
	
	/*格式化数据输出*/
	private function prase_data(&$data){
		$data['school_name'] = $this->get_school_name($data['province_id'], $data['city_id'], $data['county_id'], $data['school_id']);
		unset($data['province_id'], $data['city_id'], $data['county_id'], $data['school_id']);
		if(isset($data['class_grade']))
		{
			$data['class_grade'] = Constant::grade($data['class_grade']);
		}
	}
	
	/*根据学校ID获取省，市，县，学校名称*/
	private function get_school_name($province_id, $city_id, $county_id, $school_id){
		
		$school_name = '';
		$this->db->select('classes_area.name as area_name,classes_area.id as area_id,classes_schools.schoolname');
		$this->db->from('classes_schools,classes_area');
		$this->db->where_in('classes_area.id',array($province_id, $city_id, $county_id));
		$this->db->where('classes_schools.id',$school_id);
		$result = $this->db->get()->result_array();
		if(!empty($result))
		{
			$this->load->helper('language');
			if(!isset($result[2]))
			{
				$school_name .= $result[0]['area_name'].lang('city');
				$school_name .= $result[1]['area_name'];
				$school_name .= $result[0]['schoolname'];
			}
			else
			{
				$school_name .= $result[0]['area_name'].lang('province');
				$school_name .= $result[1]['area_name'].lang('city');
				if (isset($result[2]['area_name'])){
					$school_name .= $result[2]['area_name'];
				}
				$school_name .= $result[0]['schoolname'];
			}
		}
		return $school_name;
	}
	
	/*获取老师所在的初中或高中班级信息*/
	// hi_grade : 高年级
	//subject_id is subject_type_id, 1 to 9 in table classes_teacher
	function get_classes_by_tch_with_grades($teacher_id , $subject_id=false,$hi_grade = false){
		$this->db->select('classes.*,user.name as creator_name,classes_teacher.subject_id');
		$this->db->from('classes_teacher');
		if($hi_grade){
					$this->db->join('classes',"classes.id=classes_teacher.class_id AND classes_teacher.teacher_id={$teacher_id} and classes.class_grade >3 ",'left');
		}else{
				$this->db->join('classes',"classes.id=classes_teacher.class_id AND classes_teacher.teacher_id={$teacher_id} and classes.class_grade <=3 ",'left');
		}
		$this->db->join('user', 'user.id=classes.creator_id','left');
		if($subject_id){
				$this->db->where('classes_teacher.subject_id',$subject_id);
		}
		$this->db->where('classes.close_status',0);
		$this->db->order_by('classes.id', 'desc');
		$arr_result = $this->db->get()->result_array();
		if(!empty($arr_result))
		{
			foreach($arr_result as &$value)
			{
				$this->prase_data($value);
			}
		}
		return $arr_result;
	}

	/**
	 * 获取老师的真实姓名
	 * @param @teacher_id
	 * @return string
	 */
	public function teacher_realname($teacher_id){
		$r = $this->db->query('select name from user where id=?', array($teacher_id))->result('array');
		return isset($r[0]['name']) ? $r[0]['name'] : null;
	}

	//老师绑定某班级的教材版本
	public function bind_textbook($param){
		if(!isset($param['course_ids'])) return false;
		$c = intval($param['class_id']);
		$t = intval($param['teacher_id']);
		if(!$c || !$t) return false;
		$sql = "update classes_teacher set course_ids='{$param['course_ids']}' where teacher_id = $t and class_id=$c";
		return $this->db->query($sql);
	}
	//get 老师绑定某班级的教材版本
	public function get_bind_textbook($param){
		$c = intval($param['class_id']);
		$t = intval($param['teacher_id']);
		if(!$c || !$t) return false;
		$sql = "select course_ids from classes_teacher where teacher_id=$t and class_id=$c limit 1";
		$r=$this->db->query($sql)->result_array();
		return $r[0];
	}
	/**
	 * 获取老师在某班级内的所授课程ID
	 * @param integer $class_id
	 * @param integer $teacher_id
	 * @return integer or null
	 */
	public function teachsubj_inclass($class_id, $teacher_id){
		$r = $this->db->query('select subject_id from classes_teacher where class_id=? 
				and teacher_id=?', array($class_id, $teacher_id))->result('array');
		return isset($r[0]['subject_id']) ? $r[0]['subject_id'] : null;
	}
}

/* end of classes_teacher.php */