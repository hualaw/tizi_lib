<?php

class Redis_Model extends LI_Model {

	 public function __construct()
	 {
        parent::__construct();
		//delete_cookie(Constant::COOKIE_NOREDIS);
     }
	 
	function connect($type=null)
	{
	    $this->load->driver('cache',array('adapter'=>'redis'));
	    $this->config->load('redis',true,true);
		$redis_config=$this->config->item('redis');
		$db=$type&&isset($redis_config['redis_db'][$type])?$redis_config['redis_db'][$type]:0;
		if($this->cache->redis->select($db))
		{			
	    	return true;
		}
		else
		{
			log_message('error_tizi','17009:Redis connect failed',array('redis_db_type'=>$type));
			return false;
		}
	}

}
/* end of redis_model.php */
