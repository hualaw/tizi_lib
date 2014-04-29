<?php
/**
 *  notice_model消息相关
 *  @package models
 *  @author  jiangwuzhang
 *	@version 2.0
 */
class Notice_Model extends CI_Model {  
	
	public function mt_get($msg_type){
		$res = $this->db->query("select id,msg from notice where 
			msg_type=?", array($msg_type))->row_array();
		return $res;
	}

}
/* end of redis_model.php */
