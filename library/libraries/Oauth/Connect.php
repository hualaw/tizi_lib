<?php

class Connect{

    protected static $module;
    
    public function __construct(){

    }

    protected function get_config(){

        if(!empty(self::$config)){
            return self::$config;
        }else{
            $conf_path = APPPATH .'config/'. ENVIRONMENT . '/oauth.php'; 
            require($conf_path);       
            return $config[self::$module];
        }
    }


}
