<?php

require_once(__DIR__.DIRECTORY_SEPARATOR.'wechat.class.php');

class Wx_Base{

    protected $config;
    private $_CI;

    public function __construct(){

        $this->_CI = & get_instance(); 
        $this->_CI->load->config("oauth");
        $this->config = $this->_CI->config->item('wx');

    }


}
