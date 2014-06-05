<?php

class task_model extends LI_Model {
	
	const REDIS_T_PRE = "t_"; 
	
	/* 获取一条没有做过的任务 */
	public function rand_one($user_id){
		$logs = self::get_logs($user_id);
		$task = self::all_task();
		$date = date("Y-m-d");
		$rds = self::REDIS_T_PRE.$user_id."_".$date;
		$this->load->model("redis/redis_model");
		if ($this->redis_model->connect("task")){
			$data = $this->cache->get($rds);
		}
		
		if ($data < 0){
			return NULL;
		} else if ($data > 0){
			return isset($task[$data]) ? $task[$data] : NULL;
		} else {
			foreach ($logs as $value){
				$task_id = $value["task_id"];
				if (isset($task[$task_id])){
					unset($task[$task_id]);
				}
			}
			$rand_key = array_rand($task);
			if (NULL !== $rand_key){
				$rand_task = $task[$rand_key];
				if ($this->redis_model->connect("task")){
					$this->cache->set($rds, $rand_key, 86400);
				}
				return $rand_task;
			} else {
				return NULL;
			}
		}
	}
	
	public function close($user_id){
		$this->load->model("redis/redis_model");
		$date = date("Y-m-d");
		$rds = self::REDIS_T_PRE.$user_id."_".$date;
		if ($this->redis_model->connect("task")){
			$this->cache->set($rds, "-1", 86400);
		}
	}
	
	public function all_task(){
		$res = $this->db->query("select * from task_rule")->result_array();
		$task = array();
		foreach ($res as $value){
			$task[$value["id"]] = $value;
		}
		return $task;
	}
	
	public function get_logs($user_id){
		$res = $this->db->query("select * from task_rule_logs where 
			user_id=?", array($user_id))->result_array();
		return $res;
	}
	
	public function get_task($task_id){
		$res = $this->db->query("select * from task_rule where id=?", array($task_id))->row_array();
		return $res;
	}
	
	public function add_log($user_id, $task_id){
		$date = date("Y-m-d H:i:s");
		$this->db->query("insert ignore into task_rule_logs(user_id,task_id,cyclenum,start_date,
			last_date) values(?,?,?,?,?)", array($user_id, $task_id, 1, $date, $date));
		return $this->db->affected_rows();
	}
	
	public function get_id($name){
		$res = $this->db->query("select id from task_rule where name=?", array($name))->row_array();
		return isset($res["id"]) ? $res["id"] : 0;
	}
	
}

/* end of credit_model.php */