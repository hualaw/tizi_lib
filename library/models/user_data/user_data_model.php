<?php
require_once('data_model.php');
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-4-26
 * Time: 上午11:27
 * To change this template use File | Settings | File Templates.
 */
class User_Data_Model extends Data_Model{

	protected $_table='user_data';
	private $user_data_table = 'user_data';
	private $study_pets_table = 'study_pets';

	public function __construct()
	{
		parent::__construct();
	}

	public function get_user_data($user_id)
    {
        return parent::get_data(intval($user_id));
    }

	/**
	 * @info 获取用户资料
	 */
	//public function get_user_data($uid){
	//	return $this->db->query("select * from `{$this->user_data_table}` where `user_id` = " . intval($uid))->row();
	//}
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

	/** 获得宠物的状态
	 * @param $brush_times 刷题次数
	 * @param $last_success_time 最近刷题成功的时间
	 * @return mixed
	 */
	public function get_pet_status($brush_times, $last_success_time){
		//没有刷过题
		if ($brush_times == 0) return Constant::pet_status(1);

		$status_id = 1;
		if ($last_success_time < strtotime(date('Y-m-d', strtotime('-4 days')))) {
			$status_id = 3;
		} elseif ($last_success_time < strtotime(date('Y-m-d', strtotime('-1 days')))) {
			$status_id = 2;
		}
		return Constant::pet_status($status_id);
	}

	/** 用户经验得到相应的等级
	 * @param $exp
	 * @return int
	 */
	public function exp_to_level($exp) {
		$level = 1;

		while ((($level - 1) * ($level - 1) + 4 * ($level - 1)) < $exp) {
			$level++;
		}

		if ($level == 1) {
			return 1;
		}
		return $level - 1;
	}

	/** 用户由等级获得相应等级需要的经验
	 * @param $level
	 * @return mixed
	 */
	public function level_to_exp($level) {
		return ($level - 1) * ($level - 1) + 4 * ($level - 1);
	}

	/** 根据用户当前的经验等级 算出前台等级进度条width百分比
	 * @param $level
	 * @param $exp
	 * @param $width
	 * @return int
	 */
	public function user_level_progress($exp){
		$level = $this->exp_to_level($exp);
		$level_exp_low = ($level == 1) ? 0 : ($this->level_to_exp($level));
		$level_exp_up = $this->level_to_exp($level + 1);

		return round((($exp - $level_exp_low) / ($level_exp_up - $level_exp_low)) * 100, 2) . '%' ;
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
	/** 用户刷题等级排行
	 * @param $grade_id
	 * @param $limit
	 * @return mixed
	 */
	public function get_user_exp_rank($grade_id, $limit) {
		$sql ="SELECT user_data.exp, `user`.`name`, `user`.id FROM `user` LEFT JOIN user_data ON user_data.user_id = `user`.id WHERE user_data.grade_id = {$grade_id}  AND `user`.register_origin IN (101,102,104) ORDER BY user_data.exp DESC LIMIT {$limit}";
		//$sql = "SELECT user_id, exp FROM user_data WHERE grade_id = {$grade_id} ORDER BY exp DESC LIMIT {$limit}";
		$user_rank = $this->db->query($sql)->result();
		$this->load->model('login/register_model');
		foreach ($user_rank as $ku => &$vu) {
			if(empty($vu->user_id)){
					continue;
			}
			$users = $this->register_model->get_user_info($vu->user_id);
			$vu->user_info = $users['user'];
			$vu->pet_level = $this->exp_to_level($vu->exp);
		}
		return $user_rank;
	}

}