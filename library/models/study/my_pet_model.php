<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-4-10
 * Time: 下午2:55
 * To change this template use File | Settings | File Templates.
 */
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class My_Pet_Model extends CI_Model{
	private $pet_tabel = 'study_pets';
	private $user_pet_table = 'student_data';

	/** 根据user_id获得用户宠物的相关信息
	 * @param $user_id
	 * @return mixed
	 */
	public function get_user_pet_info($user_id) {
		$this->db->select('user_pet.*,pet.pet_path,pet.pet_exp_commission,pet.pet_description,pet.level_need');
		$this->db->from($this->user_pet_table . ' user_pet');
		$this->db->join($this->pet_tabel . ' pet', "user_pet.pet_id = pet.id", 'left');
		$this->db->where('user_id', $user_id);
		$query = $this->db->get($this->user_pet_table);
		return $query->row();
	}

	/** 用户宠物表新增数据
	 * @param $param
	 * @return bool
	 */
	public function insert_user_pet($param) {
		$this->db->trans_begin();
		$this->db->insert($this->user_pet_table, $param);
		if ($this->db->trans_complete() === false) {
			return false;
		}
		return true;
	}
	/** 修改用户宠物表
	 * @param $user_id
	 * @param $param
	 * @return bool
	 */
	public function update_user_pet($user_id, $param) {
		$this->db->trans_begin();
		$this->where("user_id = {$user_id}");
		$this->db->update($this->user_pet_table, $param);
		if ($this->db->trans_complete() === false) {
			return false;
		}
		return true;
	}

	/** 初始化用户宠物表
	 * @param $user_id
	 * @return bool
	 */
	public function init_user_pet($user_id) {
		$user_pet_info = $this->get_user_pet_info($user_id);
		if (empty($user_pet_info)) {
			$param = array(
				'user_id' => $user_id,	//用户id
				'pet_id' => 1,			//选中的宠物
				'user_exp' => 0,		//用户经验
				'user_level' => 1,		//用户等级
				'user_pet' => '1',		//用户解锁的宠物：1,2,3
				'pet_status' => '',		//宠物状态
				'brush_times' => 0		//刷题次数
			);
			return $this->insert_user_pet($param);
		}
		return true;
	}

	/** 用户经验得到相应的等级
	 * @param $exp
	 * @return int
	 */
	public function user_exp_to_level($exp) {
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
	public function user_level_to_exp($level) {
		return ($level - 1) * ($level - 1) + 4 * ($level - 1);
	}









}