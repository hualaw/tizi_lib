<?php

class Connect{

    protected static $module;
    
    protected $_CI;
    
    public function __construct(){
		$this->_CI = & get_instance();
    }

    protected function get_config(){

        if(!empty(self::$config)){
            return self::$config;
        }else{
			
            $this->_CI->load->config("oauth");
            $config = $this->_CI->config->item(self::$module);
            
            return $config;
        }
    }


}
