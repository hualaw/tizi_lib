<?php

class Classes_Area extends LI_Model{
	
	/**
	 * 通过父节点获取所有孩子节点数据[area表]
	 * @param integer $parentid
	 * @param string $fields
	 * @return array
	 * @author jiangwuzhang
	 */
	public function id_children($parentid, $fields = "*"){
		$id_replace = array(2,25,27,32,33);
		$rel = array(
			2 => 52,
			25 => 321,
			27 => 343,
			32 => 394,
			33 => 395
		);
		if (in_array($parentid, $id_replace)){
			$parentid = $rel[$parentid];
		}
		$result = $this->db->query("select {$fields} from classes_area where 
				parentid=?", array($parentid))->result_array();
		return $result;
	}
	
	public function fullname_get($area_ids = array()){
		if (is_array($area_ids)){
			$area_ids = implode(",", $area_ids);
		}
		$fullname = array();
		$res = $this->db->query("select name from classes_area where id 
			in({$area_ids})")->result_array();
		foreach ($res as $value){
			$fullname[] = $value["name"];
		}
		return implode("", $fullname);
	}
}