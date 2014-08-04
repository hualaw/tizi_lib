<?php
if ( ! defined("BASEPATH")) exit("No direct script access allowed");

class v9_position_model extends MY_Model{
	
	public function add(array $data){
		foreach ($data as $pos){
			$this->db->query("replace into v9_position(id,catid,posid,module,modelid,thumb,data,siteid,
				listorder,expiration,extention,synedit) values(?,?,?,?,?,?,?,?,?,?,?,?)", array(
				$pos["id"], $pos["catid"], $pos["posid"], $pos["module"], $pos["modelid"], 
				$pos["thumb"], $pos["data"], $pos["siteid"], $pos["listorder"], $pos["expiration"],
				$pos["extention"], $pos["synedit"]));
		}
		return $this->db->affected_rows();
	}
	
	public function position_delete(array $data){
		foreach ($data as $p){
			$this->db->query("delete from v9_position where id=? and modelid=? and posid=?", array(
				$p["id"], $p["modelid"], $p["posid"]));
		}
		return $this->db->affected_rows();
	}
	
	public function get($posid, $offset, $pagesize, $fields = "*", $order = "listorder DESC"){
		$res = $this->db->query("select {$fields} from v9_position where posid=? order by {$order} 
			limit {$offset},{$pagesize}", $posid)->result_array();
		return $res;
	}
	
}
/* end of v9_position_model.php */