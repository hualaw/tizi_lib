<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class CI_Task {

	protected $_CI;

	public function __construct (){
		$this->_CI = & get_instance();
	}
	
	public function exec($user_id, $task_name){
		$this->_CI->load->model("task/task_model");
		$task_id = $this->_CI->task_model->get_id($task_name);
		$res = $this->_CI->task_model->add_log($user_id, $task_id);
		
		if ($res === 1){
			$this->_CI->task_model->close($user_id);
		}
		return $res;
	}
	
}
