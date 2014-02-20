<?php
if (!defined("BASEPATH")) exit("No direct script access allowed");

class account_model extends LI_Model {
	
	/**
	 * 方法开头解释
	 * s_开头:学生相关
	 * t_开头:老师相关
	 * p_开头:家长相关
	 */ 
	
	
	/**
	 * 查询某个手机号的家长是否存在
	 * @return boolean
	 * true 存在
	 * false 不存在
	 */ 
	public function p_phone_exists($phone){
		$res = $this->db->query("select id from parents_create where phone=?", 
			array($phone))->result_array();
		if (isset($res[0])){
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 删除未激活的家长用户
	 * @return 删除的行数
	 * 1:删除成功
	 * 0:删除失败或者记录不存在
	 */ 
	public function p_delete($phone){
		$this->db->query("delete from parents_create where phone=? and user_id=0", array($phone));
		return $this->db->affected_rows();
	}
	
	/**
	 * 通过手机号码注册帐号
	 * @return 新增的行数
	 * 1：添加成功
	 * 0：添加失败或者用户已经存在
	 */
	public function p_addas_phone($phone, $password){
		$this->db->query("insert into parents_create(phone,password) values(?,?)", array($phone, $password));
		return $this->db->affected_rows();
	}
	
}
/* End of file account_model.php */