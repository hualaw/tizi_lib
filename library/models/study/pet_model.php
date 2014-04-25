<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 91waijiao
 * Date: 14-4-16
 * Time: 上午11:09
 * To change this template use File | Settings | File Templates.
 */
class Pet_Model extends LI_Model{
	/**获取所有的宠物
	 * @return mixed
	 */
	public function get_all_pets(){
		return $this->db->query("SELECT * FROM study_pets")->result();
	}

	/** 获取一个宠物的信息
	 * @param $pet_id
	 * @return mixed
	 */
	public function get_pet_by_id($pet_id){
		return $this->db->query("SELECT * FROM study_pets WHERE id = {$pet_id}")->row();
	}

}