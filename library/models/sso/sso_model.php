<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class sso_model extends MY_Model {
	private $_table = 'session_sso';

	public function __construct() {
		parent::__construct();
	}

	/** 根据 openid 获得一行第三方登录的信息
	 * @param $open_id
	 * @param $phone
	 * @return mixed
	 */
	public function get_sso_by_open_id($open_id) {
		$this->db->where('open_id', $open_id);
		return $this->db->get($this->_table)->row();
	}

	/** 根据sso_id 得到一行 第三方登录信息
	 * @param $sso_id
	 * @return mixed
	 */
	public function get_sso_by_id($sso_id) {
		$this->db->where('id', $sso_id);
		return $this->db->get($this->_table)->row();
	}

	/** 新增一行 第三方登录信息
	 * @param $param
	 * @return bool
	 */
	public function insert_sso($param) {
		$this->db->trans_start();
		$this->db->insert($this->_table, $param);
		$sso_id = $this->db->insert_id();
		$this->db->trans_complete();

		if ($this->db->trans_status() === false) {
			return false;
		}
		return true;
	}
	/**
	 * @param $sso_id
	 * @param $param
	 * @return bool|mixed
	 */
	public function update_sso($sso_id, $param) {
		$this->db->trans_start();
		$this->db->where('id', $sso_id);
		$this->db->update($this->_table, $param);
		$this->db->trans_complete();

		if ($this->db->trans_status() === false) {
			return false;
		}
		return true;
	}
	
	public function by_token($access_token){
		$this->db->where("access_token", $access_token);
		return $this->db->get($this->_table)->row_array();
	}
	
	public function openid_token($open_id, $access_token){
		$this->db->where("open_id", $open_id);
		$this->db->where("access_token", $access_token);
		return $this->db->get($this->_table)->row_array();
	}
}