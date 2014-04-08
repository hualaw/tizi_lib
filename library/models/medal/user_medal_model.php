<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class user_medal_model extends MY_Model {
	private $_table = 'user_medal';

	public function __construct() {
		parent::__construct();
	}

	/** 向用户登录统计表中插入数据
	 * @param $param
	 * @return bool
	 */
	public function insert_user_medal($param) {
		$this->db->trans_begin();
		$this->db->insert($this->_table, $param);

		$this->db->trans_complete();
		if ($this->db->trans_status() === false) {
			return false;
		}
		return true;
	}

	/** 更新用户登录统计表
	 * @param $user_id
	 * @param $param
	 * @return bool
	 */
	public function update_user_medal($user_id, $medal_type, $param) {
		$this->db->trans_begin();
		$this->db->update($this->_table, $param, "user_id = {$user_id} AND medal_type = {$medal_type}");
		$this->db->trans_complete();
		if ($this->db->trans_status() === false) {
			return false;
		}
		return true;
	}

	/** 获取用户登录统计表信息
	 * @param $user_id
	 * @return mixed
	 */
	public function get_user_medal_info($user_id,$medal_type) {
		$this->load->model("redis/redis_model");
		$this->redis_model->connect('medal');
//		var_dump($this->redis_model->connect('medal'));exit;

		$r_key = 'user_medal_level_' . $medal_type . '_' . $user_id;

		if ($login_statistics = $this->cache->redis->hgetall($r_key)) {
			return unserialize($login_statistics);
		}
		$this->db->where('user_id', $user_id);
		$this->db->where('medal_type', $medal_type);
		$this->db->limit(1);
		$query = $this->db->get($this->_table);
		$login_statistics = $query->row();

		$this->cache->redis->hset($r_key, serialize($login_statistics), Constant::USER_MEDAL_TIMEOUT);
		return $login_statistics;
	}


	/** 获取资深用户的达人等级
	 * @static
	 * @param $register_days
	 * @return int|string
	 */
	public function medal_register_level_days($register_level_days, $flag = 'level') {
		$level = array(
			1 => 90,
			2 => 180,
			3 => 360,
			4 => 720
		);

		if ($flag == 'level') {
			if ($register_level_days > 720) return 5;

			foreach ($level as $kl => $vl) {
				if ($register_level_days <= $vl) return $kl;
			}
		} elseif ($flag == 'days') {
			if ($register_level_days >= 5) return '您已经是最高级别了！';
			foreach ($level as $kl => $vl) {
				if ($register_level_days == $kl) return $vl;
			}
		}
	}


	/** 获取用户过去30天内，登录天数相对应的等级
	 * @static
	 * @param $login_count_level
	 * @return int|string
	 */
	public function medal_login_count_level($login_count_level, $flag = 'level') {
		$level = array(
			1 => 4,
			2 => 8,
			3 => 12,
			4 => 16,
			5 => 20,
			6 => 24,
			7 => 28,
			8 => 30
		);
		if ($flag == 'level') {
			if ($login_count_level >= 30) return 8;

			foreach ($level as $kl => $vl) {
				if ($login_count_level <= $vl) {
					return $kl;
				}
			}
		} elseif ($flag == 'count') {
			if ($login_count_level <= 1) return 4;
			if ($login_count_level >= 8) return 30;

			foreach ($level as $kl => $vl) {
				if ($login_count_level == $kl) {
					return $vl;
				}
			}
		}
	}
}