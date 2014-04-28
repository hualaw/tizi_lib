<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-4-26
 * Time: 上午11:27
 * To change this template use File | Settings | File Templates.
 */
class user_data_model extends LI_Model{

	private $user_data_table = 'user_data';
	private $study_pets_table = 'study_pets';

	public function __construct(){
		parent::__construct();
	}

	/**
	 * @info 获取用户资料
	 */
	public function get_user_data($uid){
		return $this->db->query("select * from `{$this->user_data_table}` where `user_id` = " . intval($uid))->row();
	}
	/** 获得用户和宠物的信息
	 * @param $uid
	 * @return mixed
	 */
	public function get_user_pet_data($uid){
		return $this->db->query("SELECT sd.*,sp.pet_name,sp.pet_exp_bonus,sp.pet_description,sp.level_need FROM `{$this->user_data_table}` sd LEFT JOIN `{$this->study_pets_table}` sp ON sd.pet_id = sp.id WHERE `user_id` = {$uid}")->row();
	}

	/** 初始化用户额外信息表
	 * @param $param
	 * @return bool|mixed
	 */
	public function init_user_data($param){
		if ($this->get_user_data($param['user_id'])) {
			return $this->get_user_pet_data($param['user_id']);
		}

		$this->db->trans_start();
		$this->db->insert($this->user_data_table, $param);
		if ($this->db->trans_complete() === false) {
			return false;
		}

		return $this->get_user_pet_data($param['user_id']);
	}

	/** 更新用户信息
	 * @param $uid
	 * @param $param
	 * @return bool
	 */
	public function update_user_data($uid, $param) {
		$this->db->trans_start();
		$this->db->where('user_id', $uid);
		$this->db->update($this->user_data_table, $param);
		if ($this->db->trans_complete() === false) {
			return false;
		}
		return true;
	}

	/** 用户经验得到相应的等级
	 * @param $exp
	 * @return int
	 */
	public function exp_to_level($exp) {
		$arr = array(
			4 => 1,
			11 => 2,
			20 => 3,
			31 => 4,
			44 => 5,
			60 => 6,
			77=> 7,
			95 => 8,
			116 => 9,
			139 => 10,
			164 => 11,
			191 => 12,
			220 => 13,
			252 => 14,
			285 => 15,
			319 => 16,
			356 => 17,
			395 => 18
		);

		foreach ($arr as $ka => $va) {
			if ($exp <= $ka) {
				return $va;
			}
		}
		return 18;
	}

	/** 用户由等级获得相应等级需要的经验
	 * @param $level
	 * @return mixed
	 */
	public function level_to_exp($level) {
		return ($level - 1) * ($level - 1) + 4 * ($level - 1);
	}

	/** 根据用户当前的经验等级 算出前台等级进度条的width
	 * @param $level
	 * @param $exp
	 * @param $width
	 * @return int
	 */
	public function user_level_progress($exp){
		$level = $this->exp_to_level($exp);
		$level_exp_low = ($level == 1) ? 0 : ($this->level_to_exp($level));
		$level_exp_up = $this->level_to_exp($level + 1);

		return intval((($exp - $level_exp_low) / ($level_exp_up - $level_exp_low)) * 100) . '%' ;
	}

	/** 更新用户使用过的应用的值
	 * @param $user_id 用户id
	 * @param string $app_name 使用的app的名称
	 * @return string
	 */
	public function update_user_apps($user_id, $app_name = 'tiku'){
		$app_bit = Constant::user_apps_binary($app_name);
		$user_data = $this->get_user_data($user_id);
		$original_bit = base_convert($user_data->user_apps, 10, 2);
		$bit_res = $original_bit | $app_bit;//按位或
		$result  = base_convert($bit_res, 2, 10);

		if ($user_data->user_apps == $result) return true;

		$param = array('user_apps' => $result);
		return $this->update_user_data($user_id, $param);
	}


}