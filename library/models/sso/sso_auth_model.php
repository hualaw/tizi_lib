<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class sso_auth_model extends MY_Model {
	private $_table = 'sso_auth';

	public function __construct() {
		parent::__construct();
	}

	/** 根据open_id 返回一条 上线的 sso_auth数据
	 * @param $open_id
	 * @return mixed
	 */
	public function get_sso_auth_by_open_id($auth_id) {
		$this->db->where('auth_id', $auth_id);
		$this->db->where('is_online', 1);
		return $this->db->get($this->_table)->row();
	}
}