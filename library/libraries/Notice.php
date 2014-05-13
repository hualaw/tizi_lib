<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class CI_Notice {
	
	protected $_CI;
	
	private $_split_read_limits = 1000000000;
	
	private $_redis = false;
	
	private $_redis_db = false;
	
	private static $_notice;

	public function __construct(){
		$this->_CI = & get_instance();
		$this->_CI->load->model("redis/redis_model");
		$this->_redis_db = $this->_CI->redis_model->connect("notice");
		if ($this->_redis_db){
			$this->_redis = true;
		} else {
			//log_message();
		}
	}
	
	/**
	 * @param $msg_type 消息类型
	 */
	public function add($user_id, $msg_type, $data = array(), $score = false){
		if ($this->_redis){
			if ($score === false){
				$score = time();
			}
			$data = array_merge(array("_mt" => $msg_type, "_u" => $score), $data);
			$data = json_encode($data);
			$this->_CI->cache->redis->select($this->_redis_db);
			return $this->_CI->cache->redis->zadd($user_id, $score, $data);
		}
		return false;
	}
	
	public function get($user_id, $curr_page = 1, $per_page = 10){
		$notice = array();
		if ($this->_redis){
			$offset = $per_page * ($curr_page - 1);
			$vector = $this->_CI->cache->redis->zrevrangebyscore($user_id, time(), 0, $offset, $per_page, 1);
			
			if (is_array($vector)){
				foreach ($vector as $data => $score){
					$_data = json_decode($data, true);
					if ($score > $this->_split_read_limits){
						$this->_CI->cache->redis->zincrby($user_id, (0 - $this->_split_read_limits), $data);
						$is_read = 0;
						$date = date("Y-m-d H:i:s", $score);
					} else {
						$is_read = 1;
						$date = date("Y-m-d H:i:s", $score + 1000000000);
					}
					$_content = self::template_c($_data);
					if ($_content !== null){
						$notice[] = array("content" => $_content, "date" => $date, "is_read" => $is_read);
					}
				}
			}
			return $notice;
		}
		return NULL;
	}
	
	public function total($user_id, $start_time, $last_time){
		if($this->_redis){
			return $this->_CI->cache->redis->zcount($user_id, $start_time, $last_time);
		}
	}
	
	public function total_new($user_id){
		if($this->_redis){
			return $this->_CI->cache->redis->zcount($user_id, $this->_split_read_limits, time());
		}
	}
	
	private function template_c($data){
		$this->_CI->load->model("notice/notice_model");
		$_mt = $data["_mt"];
		unset($data["_mt"]);
		if (!isset($this->_notice[$_mt])){
			$_notice = $this->_CI->notice_model->mt_get($_mt);
			if (isset($_notice["msg"])){
				$this->_notice[$_mt] = $_notice["msg"];
			}
		}
		$search = array();
		$replace = array();
		foreach ($data as $key => $value){
			$search[] = "{".$key."}";
			$replace[] = $value;
		}
		if (isset($this->_notice[$_mt])){
			return str_replace($search, $replace, $this->_notice[$_mt]);
		}
		return NULL;
	}
}
