<?php

class credit_store_model extends LI_Model {
	
	public function product_all(){
		$res = $this->db->query("select id,name,credit_price,stock,exchange_total,thumb from 
			credit_store order by id asc")->result_array();
		return $res;
	}
	
}

/* end of credit_store_model.php */