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
	public function insert_user_medal($param, $is_redis = true) {
		$this->db->trans_begin();
		$this->db->insert($this->_table, $param);

		$this->db->trans_complete();
		if ($this->db->trans_status() === false) {
			return false;
		}

		if ($is_redis) {
			$this->load->model("redis/redis_model");
			$this->redis_model->connect('medal');

			$r_key = $param['user_id'] . '_' . date('m_d', time());

			$this->cache->redis->hset($r_key, $param['medal_type'], serialize((object)$param));
		}
		return true;
	}

	/** 更新用户登录统计表
	 * @param $user_id
	 * @param $param
	 * @return bool
	 */
	public function update_user_medal($user_id, $medal_type, $param, $is_redis = true) {
		$this->db->trans_begin();
		$this->db->where("user_id = {$user_id} AND medal_type = {$medal_type}");
		$this->db->update($this->_table, $param);
		$this->db->trans_complete();
		if ($this->db->trans_status() === false) {
			return false;
		}
		//更新完数据库，有redis的话更新redis
		if ($is_redis) {
			$this->load->model("redis/redis_model");
			$this->redis_model->connect('medal');

			$r_key = $user_id . '_' . date('m_d', time());
			$this->cache->redis->hset($r_key, $medal_type, serialize((object)$param));
		}

		return true;
	}

	/** 获取用户登录统计表信息
	 * @param $user_id
	 * @return mixed
	 */
	public function get_user_medal_info($user_id, $is_redis = true) {
		$r_key = $user_id . '_' . date('m_d', time());

		if ($is_redis) {
			$this->load->model("redis/redis_model");
			$this->redis_model->connect('medal');

			if ($tmp = $this->cache->redis->hgetall($r_key)) {
				$login_statistics = array();
				foreach ($tmp as $kl => $vl) {
					$login_statistics[$kl] = unserialize($vl);
				}
				return $login_statistics;
			}
		}

		$this->db->where('user_id', $user_id);

		$query = $this->db->get($this->_table);
		$login_statistics = array();

		foreach($query->result() as $kr => $vr) {
			$login_statistics[$vr->medal_type] = $vr;

			if ($is_redis) {
				$this->cache->redis->hset($r_key, $vr->medal_type, serialize($vr));
			}
		}
		$this->cache->redis->expire($r_key, Constant::USER_MEDAL_TIMEOUT);
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


	public function init_user_medal ($uid) {
		$this->load->model('notice/notice_model');
		$this->load->model('login/register_model');
//		$this->load->model('medal/user_medal_model');

		$user_medal = $this->get_user_medal_info($uid);

		//TODO 登录达人  这个变量暂时没用 可以考虑去掉
//		$login_info = $user_medal[Constant::USER_LOGIN_MEDAL];

		//资深达人-------------------start
		$senior_medal_type = Constant::USER_REGISTER_MEDAL;
		$senior_info = !empty($user_medal[$senior_medal_type]) ? $user_medal[$senior_medal_type] : null;

		$user_info = $this->register_model->get_user_info($uid);
		$register_days = intval((time() - strtotime($user_info['user']->register_time)) / 86400);
		$senior_master_level = $this->medal_register_level_days($register_days, 'level');

		$senior_master_get_day = strtotime($user_info['user']->register_time);
		$remain_days_msg = ($senior_master_level == 5) ? '您已经是最高级别了！' : '还需要' . ($this->medal_register_level_days($senior_master_level, 'days') - $register_days) . '天即可升级。';

		$param = array();
		if (!$senior_info) {
			$param['user_id'] = $uid;
			$param['medal_type'] = $senior_medal_type;
			$param['upgrade_msg'] = $remain_days_msg;
			$param['get_date'] = $senior_master_get_day;
			$param['level'] = $senior_master_level;

			//插入数据库，插入notice
			$this->insert_user_medal($param);
			$this->notice_model->addNotify($param['user_id'], '恭喜您成功获得资深达人勋章', time());
		} else {
			//不为空的时候判断等级是否 改变
			if ($senior_master_level == $senior_info->level) {
				//TODO 这里的中文字符串判断 有好的方法就改了
				if ($remain_days_msg != $senior_info->upgrade_msg) {
					//将原有的值赋给param，后面新加字段更新值,因为后面在更新的时候要操作redis更新数据
					unset($senior_info->id);
//					print_r($senior_info);exit;
					$param = (array)$senior_info;
					$param['upgrade_msg'] = $remain_days_msg;
					$this->update_user_medal($uid, $senior_medal_type, $param);
				}
			} else {
				if ($senior_master_level != 1) {
					$pre_level_days = $this->medal_register_level_days($senior_master_level - 1, 'days');
					$senior_master_get_day = strtotime($user_info['user']->register_time + 86400 * $pre_level_days);
				} elseif ($senior_master_level == 5) {
					$senior_master_get_day = strtotime($user_info['user']->register_time + 86400 * 720);
				}
				//将原有的值赋给param，后面新加字段更新值,因为后面在更新的时候要操作redis更新数据
				unset($senior_info->id);
				$param = (array)$senior_info;
				$param['upgrade_msg'] = $remain_days_msg;
				$param['get_date'] = $senior_master_get_day;
				$param['level'] = $senior_master_level;
				//更新notice
				$this->notice_model->addNotify($uid, '您的资深达人勋章等级上升为V' . $param['level'], time());
				$this->update_user_medal($uid, $senior_medal_type, $param);
			}
		}
		//资深达人-------------------end


//		//教师认证
//		if (empty($user_medal[Constant::TEACHER_AUTHENTICATION_MEDAL])) {
//			$this->load->model('user_data/cert_model');
//			$teacher_certification = $this->cert_model->get_apply_status($uid);
//
//			if ((!empty($teacher_certification) && $teacher_certification[0]['apply_status'] == Constant::APPLY_STATUS_SUCC) || ($this->session->userdata['certification'] == 1)) {
//				$param['user_id'] = $uid;
//				$param['medal_type'] = Constant::TEACHER_AUTHENTICATION_MEDAL;
//				$param['upgrade_msg'] = '';
//				$param['get_date'] = $teacher_certification[0]['verify_time'];
//				$param['level'] = 1;
//				$this->notice_model->addNotify($uid, '恭喜您成功获得教师认证勋章！' . $param['level'], time());
//				$this->insert_user_medal($param);
//			}
//		}

		return $this->get_user_medal_info($uid);
	}


}