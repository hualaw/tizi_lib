<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class CI_Credit {

	protected $_CI;

	public function __construct (){
		$this->_CI = & get_instance();
	}
	
	public function exec($user_id, $action, $certificate = false, $msg = "", $data = array()){
		$this->_CI->load->model("credit/credit_rule_model");
		$rule = $this->_CI->credit_rule_model->action_get($action);
		$rule_log = $this->_CI->credit_rule_model->get_log($user_id, $rule["id"]);
		$date = date("Y-m-d H:i:s");
		
		if (false === $certificate){
			$certificate = $this->_CI->session->userdata("certification");
		}
		$rewardnum = $certificate == 1 ? $rule["certificate_rewardnum"] : $rule["general_rewardnum"];
		
		
		if ($rule["cycletype"] == 0 && !empty($rule_log)){	//每个只奖励一次
			return -2;
		} else if ($rule["cycletype"] == 1 && !empty($rule_log)){	//奖励周期为每天
			$start_day = date("Y-m-d", strtotime($rule_log["start_date"]));
			$today = date("Y-m-d");
			if ($start_day === $today){
				$rule_log["data"] = explode(",", $rule_log["data"]);
				if ($rewardnum != 0 && $rule_log["cyclenum"] >= $rewardnum){
					return -2;
				} else {
					if (!empty($data) && in_array($data[0], $rule_log["data"])){
						return -3;
					} else {
						$rule_log["cyclenum"] += 1;
						if (isset($data[0])){
							$rule_log["data"] = array_merge($rule_log["data"], $data);
						}
						$rule_log["last_date"] = $date;
					}
				}
			} else {
				//重新开始rule_log，删除老的log
				$this->_CI->credit_rule_model->delete_log($rule_log["id"]);
				$rule_log = array();
			}
		} else if ($rule["cycletype"] == 2 && !empty($rule_log)){	//奖励周期为整点
			$start_hour = date("Y-m-d H", strtotime($rule_log["start_date"]));
			$current_hour = date("Y-m-d H");
			if ($start_hour === $current_hour){
				$rule_log["data"] = explode(",", $rule_log["data"]);
				if ($rewardnum != 0 && $rule_log["cyclenum"] >= $rewardnum){
					return -2;
				} else {
					if (!empty($data) && in_array($data[0], $rule_log["data"])){
						return -3;
					} else {
						$rule_log["cyclenum"] += 1;
						if (isset($data[0])){
							$rule_log["data"] = array_merge($rule_log["data"], $data);
						}
						$rule_log["last_date"] = $date;
					}
				}
			} else {
				//重新开始rule_log，删除老的log
				$this->_CI->credit_rule_model->delete_log($rule_log["id"]);
				$rule_log = array();
			}
		} else if ($rule["cycletype"] == 3 && !empty($rule_log)){	//按间隔分钟
			$start_date_timestamp = strtotime($rule_log["start_date"]);
			$current_timestamp = time();
			$part_min = ($current_timestamp - $start_date_timestamp) / 60;

			if ($part_min < $rule["cycletime"]){
				$rule_log["data"] = explode(",", $rule_log["data"]);
				if ($rewardnum != 0 && $rule_log["cyclenum"] >= $rewardnum){
					return -2;
				} else {
					if (!empty($data) && in_array($data[0], $rule_log["data"])){
						return -3;
					} else {
						$rule_log["cyclenum"] += 1;
						if (isset($data[0])){
							$rule_log["data"] = array_merge($rule_log["data"], $data);
						}
						$rule_log["last_date"] = $date;
					}
				}
			} else {
				//重新开始rule_log，删除老的log
				$this->_CI->credit_rule_model->delete_log($rule_log["id"]);
				$rule_log = array();
			}
		} else if ($rule["cycletype"] == 4 && !empty($rule_log)){	//不限奖励次数
			$rule_log["data"] = explode(",", $rule_log["data"]);
			if ($rewardnum != 0 && $rule_log["cyclenum"] >= $rewardnum){
				return -2;
			} else {
				if (!empty($data) && in_array($data[0], $rule_log["data"])){
					return -3;
				} else {
					$rule_log["cyclenum"] += 1;
					if (isset($data[0])){
						$rule_log["data"] = array_merge($rule_log["data"], $data);
					}
					$rule_log["last_date"] = $date;
				}
			}
		}
		
		$this->_CI->load->model("credit/credit_model");
		if ($certificate == 1){
			$credit_change = $rule["certificate_credit"];
		} else {
			$credit_change = $rule["general_credit"];
		}
		
		if (empty($rule_log)){					//新增rule_log
			//初始化
			$rule_log = array(
				"rule_id" => $rule["id"],
				"user_id" => $user_id,
				"cyclenum" => 1,
				"data" => implode(",", $data),
				"start_date" => $date,
				"last_date" => $date
			);
		} else {
			$rule_log["data"] = implode(",", $rule_log["data"]);
		}
		
		if ($msg === ""){
			$msg = $rule["statement"];
		}
		$flag = $this->_CI->credit_model->change_add($user_id, $rule["id"], $credit_change, $msg, $rule_log);
		return $credit_change;
	}
	
	//获取一个用户的等级
	public function userlevel($user_id){
		$this->_CI->load->model("credit/credit_userlevel_model");
		$userlevel = $this->_CI->credit_userlevel_model->get($user_id);
		return $userlevel;
	}
	
	//获取与个用户的等级和特权
	public function userlevel_privilege($user_id){
		$this->_CI->load->model("credit/credit_userlevel_model");
		$this->_CI->load->model("credit/credit_privilege_model");
		$userlevel = $this->_CI->credit_userlevel_model->get($user_id);
		$_privilege = $this->_CI->credit_privilege_model->get($userlevel["id"]);
		$privilege = array();
		foreach ($_privilege as $value){
			$privilege[$value["identifier"]] = $value;
		}
		$_userlevel_privilege = array(
			"userlevel" => $userlevel,
			"privilege" => $privilege
		);
		return $_userlevel_privilege;
	}
}
