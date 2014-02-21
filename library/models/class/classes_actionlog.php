<?php
class Classes_actionlog extends LI_Model{
	
	const TEACHER_JOIN_CLASS 	= 1;			//老师加入班级动作
	const TEACHER_LEAVE_CLASS 	= 2;			//老师主动离开班级的动作（主动）
	const TEACHER_SHOT_CLASS 	= 3;			//老师被创始人开除（被动）
	const STUDENT_JOIN_CLASS 	= 4;			//学生加入班级的动作
	const STUDENT_LEAVE_CLASS 	= 5;			//学生主动离开班级（主动）
	const STUDENT_SHOT_CLASS 	= 6;			//学生被班级创始人开除（被动）
	
	/**
	 * 添加一条老师学生的动作
	 * @param integer $class_id
	 * @param integer $user_id
	 * @param integer $action_id
	 * @param integer $dateline
	 * @author jiangwuzhang
	 */
	public function create_log($class_id, $user_id, $action_id, $dateline){
		$this->db->query('insert into classes_actionlog(user_id,class_id,action_id,dateline) 
					values(?,?,?,?)', array($user_id, $class_id, $action_id, $dateline));
	}
}