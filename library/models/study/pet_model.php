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

	/** 宠物id得到相应的图片地址
	 * @param $pet_id
	 * @return mixed
	 */
	public function pet_id_to_path($pet_id){
		$arr = array(
			1 => 'myPet/cat',
			2 => 'myPet/cat'
		);
		return isset($arr[$pet_id]) ? $arr[$pet_id] : $arr[1];
	}

}