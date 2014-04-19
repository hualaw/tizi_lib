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
	
	/*
	 * 更换宠物
	*/
	public function changePet($userInfo,$pet_id)
	{
		$pets = $this->get_all_pets();
		$list = array();
		foreach ($pets as $k=>$v)
		{
			$list[] = $v->id;
		}
		if (!in_array($pet_id, $list))
		{
			return array('status'=>false,'errMsg'=>'更换宠物id错误！');	
		}
		$this->db->where('uid', $userInfo['user_id']);
		$this->db->update('student_data', array('pet_id' => $pet_id));
		$result = $this->db->affected_rows();
		return $result > 0 ? array('status'=>true,'errMsg'=>'') : array('status'=>false,'errMsg'=>'更换宠物失败！');	
	}

}